<?php
declare(strict_types=1);

namespace App\Controller\RedPrestacional;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Routing\Router;
use PhpOffice\PhpSpreadsheet\{Spreadsheet,IOFactory};
use Cake\I18n;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

/**
 * Patients Controller
 *
 * @property \App\Model\Table\PatientsTable $Patients
 * @method \App\Model\Entity\Patient[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PatientsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => [
                'Reports',
                'ReportsWithoutCheck',
            ],
        ];

        $patients = $this->paginate($this->Patients);

        $this->set(compact('patients'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function listWithResults()
    {
        $search = $this->request->getQueryParams();
        $this->paginate = [
            'contain' => [
                'Patients',
            ],
        ];
        $reports = $this->Patients->Reports->find();
        $searchByStatus = false;
        if (!empty($search)) {
            $patientsWhere = [];
            $errorPatient = '';
            if (!empty($search['document'])) {
                $coincide = preg_match('/@/', $search['document']);

                if ($coincide > 0) {
                    $errorPatient = 'No se encontro persona con el email: ' . $search['document'];
                    $patientsWhere['email LIKE'] = '%' . $search['document'] . '%';
                } else {
                    $errorPatient = 'No se encontro persona con el documento: ' . $search['document'];
                    $patientsWhere['document'] = $search['document'];
                }
            }
            if (!empty($search['company_id'])) {
                if (empty($errorPatient)) {
                    $errorPatient = 'No se encontraron personas en la empresa indicada.';
                } else {
                    $errorPatient .= ' en la empresa indicada';
                }
                $patientsWhere['company_id'] = $search['company_id'];
            }

            if (!empty($patientsWhere)) {
                $patients = $this->Patients->find()->where($patientsWhere);
                if ($patients->all()->isEmpty()) {
                    $this->Flash->error($errorPatient);
                } else {
                    $reports->where(['patient_id IN' => $patients->all()->extract('id')->toArray()]);
                }
            }

            if (!empty($search['license_type'])) {
                $reports->where(['type' => $search['license_type']]);
            }

            if (!empty($search['status'])) {
                $searchByStatus = true;
                $reports->where(['Reports.status' => $search['status']]);
            }

            if (!empty($search['start_date'])) {
                $reports->where(['Reports.created >=' => $search['start_date']]);
            }

            if (!empty($search['end_date'])) {
                $reports->where(['Reports.created <=' => $search['end_date']]);
            }

            if (!empty($search['doctor_id'])) {
                $reports->where(['Reports.doctor_id' => $search['doctor_id']]);
            }
        }

        if (!$searchByStatus) {
            $reports->where(['Reports.status IN' => $this->Patients->Reports->getStatusesOfDiagnosis()]);
        }

        $settings = [
            'order' => ['created' => 'desc'],
            'limit' => 10,
        ];

        $reports = $this->paginate($reports, $settings);
        $getLicenses = $this->Patients->Reports->getLicenses();
        $getStatuses = $this->Patients->Reports->getStatusForDoctor();
        $getAuditors = $this->Patients->Reports->Users->getDoctors();
        $companies = $this->Patients->Companies->find()->all()->combine('id', 'name');
        $this->set(compact('reports', 'getLicenses', 'getStatuses', 'search', 'getAuditors', 'companies'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    public function listWithoutResults()
    {
        $search = $this->request->getQueryParams();
        $this->paginate = [
            'contain' => [
                'Patients',
            ],
        ];
        $reports = $this->Patients->Reports->find();
        $searchByStatus = false;
        if (!empty($search)) {
            $patientsWhere = [];
            $errorPatient = '';
            if (!empty($search['document'])) {
                $coincide = preg_match('/@/', $search['document']);

                if ($coincide > 0) {
                    $errorPatient = 'No se encontro persona con el email: ' . $search['document'];
                    $patientsWhere['email LIKE'] = '%' . $search['document'] . '%';
                } else {
                    $errorPatient = 'No se encontro persona con el documento: ' . $search['document'];
                    $patientsWhere['document'] = $search['document'];
                }
            }
            if (!empty($search['company_id'])) {
                if (empty($errorPatient)) {
                    $errorPatient = 'No se encontraron personas en la empresa indicada.';
                } else {
                    $errorPatient .= ' en la empresa indicada';
                }
                $patientsWhere['company_id'] = $search['company_id'];
            }

            if (!empty($patientsWhere)) {
                $patients = $this->Patients->find()->where($patientsWhere);
                if ($patients->all()->isEmpty()) {
                    $this->Flash->error($errorPatient);
                } else {
                    $reports->where(['patient_id IN' => $patients->all()->extract('id')->toArray()]);
                }
            }

            if (!empty($search['license_type'])) {
                $reports->where(['type' => $search['license_type']]);
            }

            if (!empty($search['start_date'])) {
                $reports->where(['Reports.created >=' => $search['start_date']]);
            }

            if (!empty($search['end_date'])) {
                $reports->where(['Reports.created <=' => $search['end_date']]);
            }

            if (!empty($search['doctor_id'])) {
                $reports->where(['Reports.doctor_id' => $search['doctor_id']]);
            }
        }

        $reports->where(['status NOT IN' => $this->Patients->Reports->getStatusesOfDiagnosis()]);

        $settings = [
            'order' => ['created' => 'desc'],
            'limit' => 10,
        ];

        $reports = $this->paginate($reports, $settings);
        $getLicenses = $this->Patients->Reports->getLicenses();
        $getAuditors = $this->Patients->Reports->Users->getDoctors();
        $companies = $this->Patients->Companies->find()->all()->combine('id', 'name');
        $this->set(compact('reports', 'getLicenses', 'search', 'getAuditors', 'companies'));
    }

    /**
     * View method
     *
     * @param string|null $id Patient id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $patient = $this->Patients->get($id, [
            'contain' => [
                'Reports' => ['doctor', 'Files', 'FilesAuditor', 'Modes', 'Privatedoctors'],
                'Companies',
                'Cities' => ['Counties' => 'States']],
        ]);

        $this->set(compact('patient'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $patient = $this->Patients->newEmptyEntity();
        if ($this->request->is(['patch', 'post', 'put'])) {
            try {
                $postData = $this->request->getData();
                $patientEntity = $this->Patients->find('all');
                if (!empty($postData['email'])) {
                    $patientEntity->where(['OR' => [
                        ['document' => $postData['document']],
                        ['email' => $postData['email']],
                    ]]);
                } else {
                    $patientEntity->where(['document' => $postData['document']]);
                }

                $patientEntity = $patientEntity
                    ->contain(['Reports'])
                    ->first();
                if (!empty($patientEntity)) {
                    throw new \Exception('Ya existe una persona con ese DNI o Email.');
                }
                $patient = $this->Patients->patchEntity($patient, $postData);
                if (!$this->Patients->save($patient)) {
                    throw new \Exception('Error al generar el agente.');
                }

                $this->Flash->success(__('Se genero el agente exitosamente'));

                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                $this->Flash->error($e->getMessage());
            }
        }

        $companies = $this->Patients->Companies->getCompanies();
        $this->set(compact('patient', 'companies'));
    }

    /**
     * Add method
     *
     * @param  string|null  $action action id.
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */

    public function addWithReport(?string $action = 'list')
    {

        switch ($action) {
            case 'update':
                $data = ['error' => false];
                try {
                    if (!$this->request->is('ajax')) {
                        throw new \Exception('Request is not AJAX.');
                    }
                    if (!$this->request->is(['patch', 'post', 'put'])) {
                        throw new \Exception('Request type is not valid.');
                    }
                    $postData = $this->request->getData();
                    $postData['user_id'] = $this->Authentication->getIdentity()->id;
                    $reportEntity = $this->Patients->Reports->get($postData['id']);

                    if (empty($reportEntity)) {
                        throw new \Exception('No se encontro el registro.');
                    }

                    $reportEntity = $this->Patients->Reports->patchEntity(
                        $reportEntity,
                        $postData
                    );

                    if (!$this->Patients->Reports->save($reportEntity)) {
                        throw new \Exception('Error al generar el agente.');
                    }

                    $user = $this->Authentication->getIdentity();
                    $group = $user->groupIdentity;
                    $redirectPrefix = !empty($group) ? $group['redirect'] : '';
                    $url = Router::url('/', true);
                    $data = [
                        'error' => false,
                        'message' => 'Se genero el agente exitosamente.',
                        'goTo' => $url . $redirectPrefix,
                    ];
                    $this->Flash->success(__('Se actualizo el registro exitosamente'));
                } catch (\Exception $e) {
                    $data = [
                        'error' => true,
                        'message' => $e->getMessage(),
                    ];
                }
                $this->viewBuilder()->setClassName('Json');
                $this->set(compact('data'));
                $this->viewBuilder()->setOption('serialize', ['data']);
                break;
            case 'create':
                $data = ['error' => false];
                try {
                    if (!$this->request->is('ajax')) {
                        throw new \Exception('Request is not AJAX.');
                    }
                    if (!$this->request->is(['patch', 'post', 'put'])) {
                        throw new \Exception('Request type is not valid.');
                    }
                    $postData = $this->request->getData();
                    $postData['user_id'] = $this->Authentication->getIdentity()->id;
                    $patientEntity = $this->Patients->find('all');
                    if (!empty($postData['email'])) {
                        $patientEntity->where(['OR' => [
                            ['document' => $postData['document']],
                            ['email' => $postData['email']],
                        ]]);
                    } else {
                        $patientEntity->where(['document' => $postData['document']]);
                    }

                    $patientEntity = $patientEntity
                        ->contain(['Reports'])
                        ->first();
                    if (empty($patientEntity)) {
                        $patientEntity = $this->Patients->newEmptyEntity([]);
                    } elseif ($postData['type'] == 'new') {
                        throw new \Exception('Ya existe una persona con ese DNI o Email.');
                    }

                    if ((int)$postData['reports'][0]['askedDays'] > 2) {
                        if (
                            empty($postData['personalDoctorName'])
                            || empty($postData['personalDoctorLastname'])
                            || (empty($postData['personalDoctorMP'])
                            && empty($postData['personalDoctorMN']))
                        ) {
                            throw new \Exception('Falta informacion del Medico Particular.');
                        }
                    }

                    $privateDoctors = $this->Patients->Reports->Privatedoctors->find('all')
                        ->where(['OR' => [
                            ['license' => $postData['personalDoctorMP']],
                            ['licenseNational' => $postData['personalDoctorMN']],
                        ]])
                        ->first();
                    if (!empty($privateDoctors)) {
                        $privateDoctorsEntity = $this->Patients->Reports->Privatedoctors->get($privateDoctors->id);
                    } else {
                        $privateDoctorsEntity = $this->Patients->Reports->Privatedoctors->newEmptyEntity([]);
                    }

                    $doctorData = [
                        'name' => $postData['personalDoctorName'],
                        'lastname' => $postData['personalDoctorLastname'],
                        'license' => $postData['personalDoctorMP'],
                        'licenseNational' => $postData['personalDoctorMN'],
                    ];
                    $privateDoctorsEntity = $this->Patients->Reports->Privatedoctors->patchEntity(
                        $privateDoctorsEntity,
                        $doctorData,
                    );

                    if ($this->Patients->Reports->Privatedoctors->save($privateDoctorsEntity)) {
                        $postData['reports'][0]['privatedoctor_id'] = $privateDoctorsEntity->id;
                    }

                    $postData['reports'][0]['user_id'] = $this->Authentication->getIdentity()->id;

                    $patientEntity = $this->Patients->patchEntity(
                        $patientEntity,
                        $postData,
                        ['associated' => ['Reports']]
                    );
                    if (!$this->Patients->save($patientEntity)) {
                        throw new \Exception('Error al generar el agente.');
                    }
                    $this->loadComponent('Messenger');
                    $this->Messenger->setToAuditor($patientEntity);
                    $user = $this->Authentication->getIdentity();
                    $group = $user->groupIdentity;
                    $redirectPrefix = !empty($group) ? $group['redirect'] : '';
                    $url = Router::url('/', true);
                    $data = [
                        'error' => false,
                        'message' => 'Se genero el agente exitosamente.',
                        'goTo' => $postData['go_to'] == 2
                            ?  $url . $redirectPrefix . 'licencias/editar/' .  $patientEntity->reports[0]->id
                            : $url . $redirectPrefix,
                    ];
                    $this->Flash->success(__('Se genero el agente exitosamente'));
                } catch (\Exception $e) {
                    $data = [
                        'error' => true,
                        'message' => $e->getMessage(),
                    ];
                }
                $this->viewBuilder()->setClassName('Json');
                $this->set(compact('data'));
                $this->viewBuilder()->setOption('serialize', ['data']);
                break;
            case 'list':
            default:
                break;
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Patient id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $patient = $this->Patients->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $patient = $this->Patients->patchEntity($patient, $this->request->getData());
            if ($this->Patients->save($patient)) {
                $this->Flash->success(__('Se guardo correctamente'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Ups, hubo un problema. Intentanuevamente'));
        }
        $this->set(compact('patient'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Patient id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $patient = $this->Patients->get($id);
        if ($this->Patients->delete($patient)) {
            $this->Flash->success(__('The patient has been deleted.'));
        } else {
            $this->Flash->error(__('The patient could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Search method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function search()
    {
        $this->viewBuilder()->setLayout('ajax');
        $document = $this->request->getQuery('dni');
        $type = $this->request->getQuery('type');
        if (empty($document)) {
            $type = 'new';
        }

        if ($type == 'new') {
            $patient = $this->Patients->newEmptyEntity();
        } else {
            $patient = $this->Patients->find('all')->where(['document' => $document])->first();
        }

        $doctors = $this->Patients->Reports->Users->getDoctors();
        $licenses = $this->Patients->Reports->getLicenses();
        $modes = $this->Patients->Reports->Modes->find()->all()->combine('id', 'name');
        $companies = $this->Patients->Companies->getCompanies();
        $specialties = $this->Patients->Reports->Specialties->find()->all()->combine('id', 'name');
        $this->set(compact('patient', 'doctors', 'licenses', 'type', 'companies', 'modes', 'specialties'));
    }

    public function result($id)
    {
        try {
            $user = $this->Authentication->getIdentity();
            $group = $user->groupIdentity;
            $redirectPrefix = !empty($group) ? $group['prefix'] : '';
            $actualPrefix = $this->request->getParam('prefix');
            if ($redirectPrefix != $actualPrefix) {
                throw new UnauthorizedException('No tienes permisos para ver esto.');
            }

            $this->loadComponent('Htmltopdf');
            $report = $this->Patients->Reports->get($id, [
                'contain' => ['doctor', 'Patients' => ['Companies', 'Cities']],
            ]);
            if (!in_array($report->status, $this->Patients->Reports->getActiveStatuses())) {
                $this->Htmltopdf->createReport($report);
            } else {
                throw new RecordNotFoundException('El reporte no esta listo');
                $this->Flash->error(__('El reporte no esta listo.'));
            }
        } catch (\Exception $e) {
            $this->Flash->error($e->getMessage(), ['escape' => false]);
            if (stripos(get_class($e), 'RecordNotFoundException')) {
                return $this->redirect(['action' => 'index']);
            } elseif (stripos(get_class($e), 'UnauthorizedException')) {
                return $this->redirect(['action' => 'edit', $id]);
            }
        }
    }

    public function viewReport($id)
    {
        try {
            $report = $this->Patients->Reports->get($id, [
                'contain' => [
                    'Files',
                    'FilesAuditor',
                    'Patients' => ['Companies', 'Cities' => ['Counties' => 'States']],
                    'Modes',
                    'Privatedoctors',
                ],
            ]);
            if (empty($report)) {
                throw new RecordNotFoundException('No se encontro el ID.');
            }

            if (in_array($report->status, $this->Patients->Reports->getActiveStatuses())) {
                throw new UnauthorizedException('El agente no se encuentra diagnosticado');
            }
        } catch (\Exception $e) {
            $this->Flash->error($e->getMessage(), ['escape' => false]);
            if (stripos(get_class($e), 'RecordNotFoundException')) {
                return $this->redirect(['action' => 'index']);
            } elseif (stripos(get_class($e), 'UnauthorizedException')) {
                return $this->redirect(['action' => 'edit', $id]);
            }
        }
        $getStatuses = $this->Patients->Reports->getStatusForDoctor();
        $this->set(compact('report', 'getStatuses'));
    }

    public function editReport($id)
    {
        try {
            $report = $this->Patients->Reports->get($id, [
                'contain' => [
                    'Privatedoctors',
                    'Specialties',
                    'Patients' => [
                        'Companies',
                        'Cities' => [
                            'Counties' => 'States',
                        ],
                    ]],
            ]);
            if (empty($report)) {
                throw new RecordNotFoundException('No se encontro el ID.');
            }

            if (!in_array($report->status, $this->Patients->Reports->getActiveStatuses())) {
                throw new UnauthorizedException('El agente se encuentra diagnosticado');
            }
        } catch (\Exception $e) {
            $this->Flash->error($e->getMessage(), ['escape' => false]);
            if (stripos(get_class($e), 'RecordNotFoundException')) {
                return $this->redirect(['action' => 'index']);
            } elseif (stripos(get_class($e), 'UnauthorizedException')) {
                return $this->redirect(['action' => 'viewReport', $id]);
            }
        }

        $doctors = $this->Patients->Reports->Users->getDoctors();
        $privateDoctors = $this->Patients->Reports->Privatedoctors->find()->all()->combine('id', function ($entity) {

            return $entity->name . ' ' .  $entity->lastname . ' (M.P: ' . $entity->license . ' - M.N:' . $entity->licenseNational . ')';
        });
        $licenses = $this->Patients->Reports->getLicenses();
        $companies = $this->Patients->Companies->getCompanies();
        $modes = $this->Patients->Reports->Modes->find()->all()->combine('id', 'name');
        $specialties = $this->Patients->Reports->Specialties->find()->all()->combine('id', 'name');

        $this->set(compact('report', 'specialties', 'doctors', 'licenses', 'companies', 'modes', 'privateDoctors'));
    }

    public function deleteReport($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $report = $this->Patients->Reports->get($id);
        if (in_array($report->status, $this->Patients->Reports->getActiveStatuses())) {
            if ($this->Patients->Reports->delete($report)) {
                $this->Flash->success(__('El reporte se elimino.'));
            } else {
                $this->Flash->error(__('El reporte no pudo ser eliminado, intente nuevamente.'));
            }
        } else {
            $this->Flash->error(__('No se puede eliminar ausentes con diagnóstico.'));
        }

        return $this->redirect(['action' => 'listWithoutResults']);
    }

    public function addDoctor($id = null)
    {
        $isNew = false;
        if (is_null($id)) {
            $isNew = true;
            $privateDoctor = $this->Patients->Reports->Privatedoctors->newEmptyEntity();
        } else {
            $privateDoctor = $this->Patients->Reports->Privatedoctors->get($id);
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $error = true;
            try {
                $postData = $this->request->getData();
                $privateDoctorEntity = $this->Patients->Reports->Privatedoctors->find('all')
                    ->where(['OR' => [
                        ['license' => $postData['license']],
                        ['licenseNational' => $postData['licenseNational']],
                    ]]);
                if (!$isNew) {
                    $privateDoctorEntity->where(['id !=' => $postData['id']]);
                }

                $privateDoctorEntity = $privateDoctorEntity->first();
                if (!empty($privateDoctorEntity)) {
                    throw new \Exception('Ya existe un medico con la licencia ingresada.');
                }
                $privateDoctor = $this->Patients->Reports->Privatedoctors->patchEntity($privateDoctor, $postData);
                if (!$this->Patients->Reports->Privatedoctors->save($privateDoctor)) {
                    throw new \Exception('Error al generar el medico.');
                }

                $license = '';
                if (!empty($privateDoctor->license)) {
                    $license .= '(M.P: ' . $privateDoctor->license;
                }

                if (!empty($privateDoctor->licenseNational)) {
                    if (empty($license)) {
                        $license = ' (';
                    } else {
                        $license .= ' - ';
                    }
                    $license .= 'M.N: ' . $privateDoctor->licenseNational . ')';
                } else {
                    $license .= ')';
                }

                $privateDoctor = [
                    'id' => $privateDoctor->id,
                    'name' => $privateDoctor->name .  ' ' . $privateDoctor->lastname . ' ' . $license,
                ];
                $message = 'Se genero correctametne el medico';
                $error = false;
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }

            $data = ['error' => $error, 'message' => $message, 'privatedoctor' => $privateDoctor];
            $this->viewBuilder()->setClassName('Json');
            $this->set(compact('data'));
            $this->viewBuilder()->setOption('serialize', ['data']);
        }

        $this->set(compact('privateDoctor'));
    }
    public function reportExcel(){
        $patients = $this->Patients->find()->contain([
            'Cities' => ['Counties' => 'States'],
            'Companies',
            'Reports' => ['Cie10', 'Modes', 'Privatedoctors','doctor', 'Specialties', 'Files', 'FilesAuditor'],
            'Cities' => ['Counties' => 'States']
   
        ]);
        $licenses = $this->Patients->Reports->getLicenses();
        $statuses = $this->Patients->Reports->getAllStatuses();

        $patientsList = $patients->all()->toArray();
        $numList = $patients->all()->count();
          /*  debug ($patientsList);
        die();  */ 
       if ($numList!=0){
        $fila=2;
        $spreadsheet = new Spreadsheet();
        $activeSheet= $spreadsheet->getActiveSheet();
        $styleArray=[
            'font'=>['bold'=>true],
            'alignment'=>[
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => [
                      'outline' => [
                        'borderStyle'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                      ]      
                ]    
            ];
        $styleArrayTable= [
            'alignment'=>[
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            
                'vertical' =>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER ]
        ];
         //-----------------------titulos----------------------------------------
         $activeSheet->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
         ->getStartColor()->setARGB('FF00aa99'); 
         $activeSheet->getStyle('A1:Q1')->applyFromArray($styleArray);
         $activeSheet->getColumnDimension('A')->setWidth(10);
         $activeSheet->setCellValue('A1','#');
         $activeSheet->getColumnDimension('B')->setWidth(20);
         $activeSheet->setCellValue('B1','NOMBRE COMPLETO');
         $activeSheet->getColumnDimension('C')->setWidth(20);
         $activeSheet->setCellValue('C1','CUIL');
         $activeSheet->getColumnDimension('D')->setWidth(10);
         $activeSheet->setCellValue('D1','EDAD');
         $activeSheet->getColumnDimension('E')->setWidth(35);
         $activeSheet->setCellValue('E1','EMPRESA');
         $activeSheet->getColumnDimension('F')->setWidth(25);
         $activeSheet->setCellValue('F1','CARGO');
         $activeSheet->getColumnDimension('G')->setWidth(10);
         $activeSheet->setCellValue('G1','ANTIGUEDAD');
         $activeSheet->getColumnDimension('H')->setWidth(35);
         $activeSheet->setCellValue('H1','ESPECIALIDAD');
         $activeSheet->getColumnDimension('I')->setWidth(25);
         $activeSheet->setCellValue('I1','ESTADO');
         $activeSheet->getColumnDimension('J')->setWidth(25);
         $activeSheet->setCellValue('J1','FECHA INICIO LICENCIA');
         $activeSheet->getColumnDimension('K')->setWidth(25);
         $activeSheet->setCellValue('K1','TIPO DE LICENCIA');
         $activeSheet->getColumnDimension('L')->setWidth(15);
         $activeSheet->setCellValue('L1','DIAS PEDIDOS');
         $activeSheet->getColumnDimension('M')->setWidth(15);
         $activeSheet->setCellValue('M1','DIAS OTORGADOS');
         $activeSheet->getColumnDimension('N')->setWidth(20);
         $activeSheet->setCellValue('N1','AUDITOR');
         $activeSheet->getColumnDimension('O')->setWidth(45);
         $activeSheet->setCellValue('O1','DICTAMEN');
         $activeSheet->getColumnDimension('P')->setWidth(25);
         $activeSheet->setCellValue('P1','NUMERO CIE10');
         $activeSheet->getColumnDimension('Q')->setWidth(25);
         $activeSheet->setCellValue('Q1','FECHA AUDITORIA');

          //---------------------------------------------Fin de encabezado------------------------
        for($i=0;$i<$numList;$i++){
             if(!empty($patientsList[$i]['reports'] )){ 
                $activeSheet->setCellValue('A'.$fila,$patientsList[$i]['id']);
                $activeSheet->setCellValue('B'.$fila,$patientsList[$i]['name'].$patientsList[$i]['lastname']);
                $activeSheet->setCellValue('C'.$fila,$patientsList[$i]['document']);
                $activeSheet->setCellValue('D'.$fila,$patientsList[$i]['age']);
                $activeSheet->setCellValue('E'.$fila,$patientsList[$i]['company']->name);
                $activeSheet->setCellValue('F'.$fila,$patientsList[$i]['job']);
                $activeSheet->setCellValue('G'.$fila,$patientsList[$i]['seniority']);
                $cantidad_reportes= count($patientsList[$i]['reports'])-1;
                isset($patientsList[$i]['reports'][$cantidad_reportes]['specialty']->name)?$activeSheet->setCellValue('H'.$fila,$patientsList[$i]['reports'][$cantidad_reportes]['specialty']->name):$activeSheet->setCellValue('H'.$fila,'');
                $status=$patientsList[$i]['reports'][$cantidad_reportes]['status'];
                $activeSheet->setCellValue('I'.$fila,$statuses[$status]);
                $frozenDate= $patientsList[$i]['reports'][$cantidad_reportes]['startLicense'];
                $startLicense=isset($frozenDate)?$frozenDate->i18nFormat('dd-MM-YYY'):'No Registrado';
                $activeSheet->setCellValue('J'.$fila,$startLicense);
                $type= $patientsList[$i]['reports'][$cantidad_reportes]['type'];
                $activeSheet->setCellValue('K'.$fila,$licenses[$type]);
                $activeSheet->setCellValue('L'.$fila,$patientsList[$i]['reports'][$cantidad_reportes]['askedDays']);
                $activeSheet->setCellValue('M'.$fila,$patientsList[$i]['reports'][$cantidad_reportes]['recommendedDays']);
                $dictamen=isset($patientsList[$i]['reports'][$cantidad_reportes]['cie10'])?$patientsList[$i]['reports'][$cantidad_reportes]['cie10']->name:'No especificado';
                $activeSheet->setCellValue('N'.$fila,$patientsList[$i]['reports'][$cantidad_reportes]['doctor']->name.' '.$patientsList[$i]['reports'][$cantidad_reportes]['doctor']->lastname);
                $activeSheet->setCellValue('O'.$fila,$dictamen);
                $codeCie10=isset($patientsList[$i]['reports'][$cantidad_reportes]['cie10'])?$patientsList[$i]['reports'][$cantidad_reportes]['cie10']->code:'No especificado';
                $activeSheet->setCellValue('P'.$fila,$codeCie10);
                $frozenDateCreated= $patientsList[$i]['reports'][$cantidad_reportes]['created'];
                $auditoriaCreation=isset($frozenDateCreated)?$frozenDateCreated->i18nFormat('dd-MM-YYY'):'No Registrado';
                //debug($frozenDateCreated);
                $activeSheet->setCellValue('Q'.$fila,$auditoriaCreation);
                $fila++;        
            } //fin de if dentro de report
        }//fin de for
       }//fin de if
        //die();
        $activeSheet->getStyle('A2:Q'.$fila)->applyFromArray($styleArrayTable);
        //die();
        $now=FrozenDate::parse('now');
        $now= $now->i18nFormat('dd-MM-Y');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename=Agentes_'.$now.'.xlsx');
		header('Cache-Control: max-age=0');
		$writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');   
        $this->redirect(['action'=>'listWithResults']);
       
    }//fin de function
}
