<?php

namespace App\Http\Controllers\PsapIndexer;

use App\Http\Controllers\Controller;
use App\Http\Models\Psap;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class PsapIndexerController extends Controller
{
    private $apiEndpoint = null;
    private $apiFormat   = null;
    private $tempStorage = null;

    /**
     * PsapIndexerController constructor.
     * @param string $tempStorage Path to store temp files
     */
    public function __construct(string $tempStorage = '../storage/app/temp')
    {
        parent::__construct();

        $this->tempStorage = $tempStorage;
        $this->apiEndpoint = \env('CIT_TEXT_REGISTRY_ENDPOINT');
        if (!$this->apiEndpoint) {
            throw new \Error('.env missing registry endpoint');
        }

        if (!File::isDirectory($this->tempStorage)) {
            File::makeDirectory($this->tempStorage);
        }

        $basicValidators = [
            'int'       => function ($input) {
                return \is_numeric($input);
            },
            'string'    => function ($input) {
                return \is_string($input) || $input === '';
            },
            'bool'      => function ($input) {
                return \is_bool($input);
            },
            'date_time' => function ($input) {
                return $input instanceof \DateTime;
            },
            'any'       => function () {
                return true;
            }
        ];

        $basicSanitizers = [
            'trim'   => function ($value) {
                return \trim($value);
            },
            'bool'   => function ($value) {
                return !!\trim($value);
            },
            'string' => function ($value) {
                return \trim($value ?? '');
            },
            'int'    => function ($value) {
                return \is_numeric($value) ? (int)$value : null;
            },
        ];

        $this->apiFormat = [
            [
                'name'        => 'state',
                'type'        => 'string',
                'description' => 'Two character state abbreviation',
                'sanitizer'   => $basicSanitizers['trim'],
                'validator'   => function ($str) {
                    return \strlen($str) === 2;
                }
            ],
            [
                'name'        => 'psap_id',
                'type'        => 'int',
                'description' => 'Public Safety Answering Point (PSAP) unique identification number',
                'sanitizer'   => $basicSanitizers['int'],
                'validator'   => $basicValidators['int']
            ],
            [
                'name'        => 'name',
                'type'        => 'string',
                'description' => 'PSAP Name',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'county',
                'type'        => 'string',
                'description' => 'US county',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'admin_name',
                'type'        => 'string',
                'description' => 'Name of person in charge of PSAP',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'admin_title',
                'type'        => 'string',
                'description' => 'Title of person in charge of PSAP',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'address',
                'type'        => 'string',
                'description' => 'Street address of PSAP',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'city',
                'type'        => 'string',
                'description' => 'City of PSAP',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'address_state',
                'type'        => 'string',
                'description' => 'Repeat of column one, discard this',
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'zip',
                'type'        => 'int',
                'description' => '5-digit US zip code',
                'sanitizer'   => function ($zip) {
                    $sanitizedZip = $zip;

                    if (strpos($zip, '-') !== false) {
                        $sanitizedZip = substr($zip, 0, strpos($zip, '-'));
                    }

                    // if zip doesn't match validation, just exclude it
                    if (\strlen($sanitizedZip) !== 5) {
                        $sanitizedZip = null;
                    }

                    return $sanitizedZip;
                },
                'validator'   => function ($zip) {
                    return \is_null($zip) || (\is_numeric($zip) && \strlen($zip) === 5);
                }
            ],
            [
                'name'        => 'phone_primary',
                'type'        => 'string',
                'description' => 'Ten-digit hyphen-delimited phone number, may contain extra caption characters',
                'sanitizer'   => function ($phone) {
                    $sanitizedPhone = $phone;

                    if (\is_string($phone)) {
                        $sanitizedPhone = (int)\str_replace('-', '', $phone);
                    }

                    if (\strlen($phone) !== 10) {
                        $sanitizedPhone = null;
                    }

                    return $sanitizedPhone;
                },
                'validator'   => function ($phone) {
                    return \is_null($phone) || (\is_numeric($phone) && \strlen($phone) === 10);
                }
            ],
            [
                'name'        => 'phone_secondary',
                'type'        => 'string|null',
                'description' => 'Ten-digit hyphen-delimited secondary phone number, may be blank. Currently discarded.',
                'validator'   => $basicValidators['any']
            ],
            [
                'name'        => 'admin_email',
                'type'        => 'string',
                'description' => 'Standard email address contact, may contain multiple separated by 1), 2)',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'supports_tty',
                'type'        => 'bool',
                'description' => '',
                'sanitizer'   => $basicSanitizers['bool'],
                'validator'   => $basicValidators['bool']
            ],
            [
                'name'        => 'supports_web',
                'type'        => 'bool',
                'description' => '',
                'sanitizer'   => $basicSanitizers['bool'],
                'validator'   => $basicValidators['bool']
            ],
            [
                'name'        => 'supports_ip',
                'type'        => 'bool',
                'description' => '',
                'sanitizer'   => $basicSanitizers['bool'],
                'validator'   => $basicValidators['bool']
            ],
            [
                'name'        => 'supports_rtt',
                'type'        => 'string',
                'description' => 'Discarded',
                'validator'   => $basicValidators['any']
            ],
            [
                'name'        => 'supports_other',
                'type'        => 'string',
                'description' => '',
                'validator'   => $basicValidators['any']
            ],
            [
                'name'        => 'admin_authority',
                'type'        => 'string',
                'description' => 'Name of entity authorizing receipt of alerts',
                'validator'   => $basicValidators['string']
            ],
            [
                'name'        => 'ready_at',
                'type'        => 'DateTime',
                'description' => 'PSAP ready date',
                'validator'   => $basicValidators['date_time']
            ],
            [
                'name'        => 'compliant_at',
                'type'        => 'DateTime',
                'description' => 'PSAP compliance date',
                'validator'   => $basicValidators['date_time']
            ],
            [
                'name'        => 'notes',
                'type'        => 'string',
                'description' => 'Optional notes',
                'sanitizer'   => $basicSanitizers['string'],
                'validator'   => $basicValidators['string']
            ]
        ];
    }

    /**
     * @param bool $returnsHttpResponse Default when accessed via HTTP. Changes to string when accessed via CLI
     * @return \Illuminate\Http\returnsHttpResponse
     */
    public function index(bool $returnsHttpResponse = true)
    {
        // execution time
        $tStart               = \microtime(true);
        $apiResponse          = \file_get_contents($this->apiEndpoint);
        $apiTargetSheetNumber = 1;

        if ($apiResponse !== false) {

            try {
                // save file to temp storage
                $tempFilePath = $this->tempStorage . '/~lookup-' . \microtime();
                File::put($tempFilePath, $apiResponse);

                // read
                $reader = ReaderFactory::create(Type::XLSX);
                $reader->open($tempFilePath);

                $formattedResults = [];
                $skippedRows      = [];

                // keep track of total rows in targeted sheet
                $rowCount = 0;

                foreach ($reader->getSheetIterator() as $sheetNumber => $sheet) {
                    if ($sheetNumber === $apiTargetSheetNumber) {
                        foreach ($sheet->getRowIterator() as $row) {
                            // pad row if needed
                            $sanitizedRow = \array_pad($row, \count($this->apiFormat), null);
                            $pass         = true;

                            // loop based on map, instead of row, guaranteeing strict
                            // behavior regardless of data
                            foreach ($this->apiFormat as $columnIndex => $columnFormat) {
                                $columnValue = $sanitizedRow[$columnIndex];

                                // sanitize
                                if (isset($columnFormat['sanitizer'])) {
                                    $sanitizedRow[$columnIndex] = $columnValue = $columnFormat['sanitizer']($columnValue);
                                }

                                // validate each value
                                if ($columnFormat['validator']($columnValue) !== true) {
                                    $pass          = false;
                                    $skippedRows[] = [
                                        'data'        => $sanitizedRow,
                                        'coordinates' => [$rowCount + 1, $columnIndex + 1],
                                        'failed_on '  => $columnFormat['name']
                                    ];
                                    break;
                                }
                            }

                            if ($pass) {
                                $formattedResult = [];
                                foreach ($this->apiFormat as $columnIndex => $columnFormat) {
                                    $formattedResult[$columnFormat['name']] = $sanitizedRow[$columnIndex];
                                }

                                $formattedResults[] = $formattedResult;
                            }

                            //
                            $rowCount++;
                        }
                    }
                }

                // cleanup
                $reader->close();
                File::delete($tempFilePath);

                if (count($formattedResults) > 0) {
                    foreach ($formattedResults as $result) {
                        Psap::updateOrCreate(['psap_id' => $result['psap_id']], [
                            'state'           => $result['state'],
                            'name'            => $result['name'],
                            'county'          => $result['county'],
                            'city'            => $result['city'],
                            'address'         => $result['address'],
                            'zip'             => $result['zip'],
                            'admin_authority' => $result['admin_authority'],
                            'admin_name'      => $result['admin_name'],
                            'admin_email'     => $result['admin_email'],
                            'admin_phone'     => $result['phone_primary'],
                            'admin_title'     => $result['admin_title'],
                            'compliant_at'    => $result['compliant_at'],
                            'ready_at'        => $result['ready_at'],
                            'supports_tty'    => !!$result['supports_tty'],
                            'supports_web'    => !!$result['supports_web'],
                            'supports_ip'     => !!$result['supports_ip'],
                            'meta'            => [
                                'notes'  => $result['notes'],
                                'source' => 'automation'
                            ]
                        ]);
                    }
                    $response = [
                        'stats' => [
                            'total_results'      => count($formattedResults),
                            'total_rows'         => $rowCount,
                            'omitted_rows'       => count($skippedRows),
                            'processed_in'       => \microtime(true) - $tStart,
                            'duplicate_psap_ids' => \array_filter(
                                \array_count_values(
                                    \array_pluck($formattedResults, 'psap_id')),
                                function ($value) {
                                    return $value > 1;
                                }
                            ),
                            'skipped_log'        => $skippedRows
                        ]
                    ];

                    if ($returnsHttpResponse) {
                        return Response::json($response, 200);
                    }

                    return \json_encode($response);
                } else {
                    $response = [
                        'status'   => 'Data did not load or match known format',
                        'endpoint' => $this->apiEndpoint
                    ];

                    if ($returnsHttpResponse) {
                        return Response::json($response, 502);
                    }

                    return \json_encode($response);
                }
            } catch (\Exception $err) {
                $response = [
                    'status' => 'Internal Server Error: ' . $err->getMessage()
                ];

                if ($returnsHttpResponse) {
                    Response::json($response, 500);
                }

                return \json_encode($response);
            }
        }
        $response = [
            'status'   => 'Service Unavailable',
            'endpoint' => $this->apiEndpoint
        ];

        if ($returnsHttpResponse) {
            return Response::json($response, 502);
        }

        return \json_encode($response);
    }
}
