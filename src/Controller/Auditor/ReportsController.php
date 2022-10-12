<?php
declare(strict_types=1);

namespace App\Controller\Auditor;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\UnauthorizedException;

/**
 * Reports Controller
 *
 * @property \App\Model\Table\ReportsTable $Reports
 * @method \App\Model\Entity\Report[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ReportsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $search = $this->request->getQueryParams();
        $this->paginate = [
            'contain' => [
                'Patients',
            ],
        ];
        $reports = $this->Reports->find();
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
                $patients = $this->Reports->Patients->find()->where($patientsWhere);
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
                $reports->where(['status' => $search['status']]);
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

        $settings = [
            'order' => ['created' => 'desc'],
            'limit' => 10,
        ];

        $reports = $this->paginate($reports, $settings);
        $getLicenses = $this->Reports->getLicenses();
        $getStatuses = $this->Reports->getAllStatuses();
        $getAuditors = $this->Reports->Users->getDoctors();
        $companies = $this->Reports->Patients->Companies->find()->all()->combine('id', 'name');

        $this->set(compact('reports', 'getLicenses', 'getStatuses', 'search', 'getAuditors', 'companies'));
    }

    /**
     * withOutDiagnostic method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function withOutDiagnostic()
    {

        $search = $this->request->getQueryParams();
        $this->paginate = [
            'contain' => [
                'Patients',
                'Users',
            ],
        ];
        $reports = $this->Reports->find();
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
                $patients = $this->Reports->Patients->find()->where($patientsWhere);
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
        }

        $reports
            ->where(['status IN' => $this->Reports::ACTIVE])
            ->where(['doctor_id' => $this->Authentication->getIdentity()->id]);

        $settings = [
            'order' => ['created' => 'desc'],
            'limit' => 10,
        ];

        $reports = $this->paginate($reports, $settings);
        $getLicenses = $this->Reports->getLicenses();
        $getStatuses = $this->Reports->getAllStatuses();
        $getAuditors = $this->Reports->Users->getDoctors();
        $companies = $this->Reports->Patients->Companies->find()->all()->combine('id', 'name');

        $this->set(compact('reports', 'getLicenses', 'getStatuses', 'search', 'getAuditors', 'companies'));
    }

    public function edit($id)
    {
        try {
            $report = $this->Reports->get($id, [
                'contain' => [
                    'Patients' => 'Companies',
                    'Files',
                ],
            ]);
            if (empty($report)) {
                throw new RecordNotFoundException('No se encontro el ID.');
            }

            if ($report->doctor_id != $this->Authentication->getIdentity()->id) {
                throw new RecordNotFoundException('El doctor Auditor no corresponde.');
            }

            if (!in_array($report->status, $this->Reports->getActiveStatuses())) {
                throw new UnauthorizedException('El agente ya se encuentra diagnosticado');
            }

            if ($this->request->is(['patch', 'post', 'put'])) {
                $data = $this->request->getData();
                $errors = '';
                if (empty($data['status']) || in_array($data['status'], $this->Reports->getActiveStatuses())) {
                    $errors = 'El estado es incorrecto.';
                }

                if (in_array($data['status'], $this->Reports->getDeniedStatus())) {
                    $data['recommendedDays'] = 0;
                    $data['startLicense'] = null;
                    $data['cie10'] = null;
                } else {
                    if (empty($data['recommendedDays']) || $data['recommendedDays'] <= 0) {
                        $errors .= '</br>Los días recomendados deben ser mayor a 0.';
                    }

                    if (
                        empty($data['startLicense'])
                        || strtotime($data['startLicense']) < strtotime('-1 days')
                    ) {
                        $errors .= '</br>La fecha de inicio no puede ser menor a ayer.';
                    }

                    if (empty($data['cie10'])) {
                        $errors .= '</br>El diagnóstico CIE 10 no puede estar vacio.';
                    }
                }

                if (!empty($errors)) {
                    throw new \Exception($errors);
                }

                $report = $this->Reports->patchEntity(
                    $report,
                    $data,
                    ['associated' =>
                        [
                            'Patients',
                        ],
                    ]
                );
                if ($this->Reports->save($report)) {
                    $this->Flash->success(__('El diagnóstico fue guardado correctamente.'));

                    return $this->redirect(['action' => 'withOutDiagnostic']);
                } else {
                    throw new \Exception('Ups, hubo un problema. Intentanuevamente');
                }
            }
        } catch (\Exception $e) {
            $this->Flash->error($e->getMessage(), ['escape' => false]);
            if (stripos(get_class($e), 'RecordNotFoundException')) {
                return $this->redirect(['action' => 'withOutDiagnostic']);
            } elseif (stripos(get_class($e), 'UnauthorizedException')) {
                return $this->redirect(['action' => 'view', $id]);
            }
        }
        $getStatuses = $this->Reports->getStatusForDoctor();
        $doctors = $this->Reports->Users->getDoctors();
        $licenses = $this->Reports->getLicenses();
        $companies = $this->Reports->Patients->Companies->getCompanies();
        $privateDoctors = $this->Reports->Privatedoctors->find()->all()->combine('id', function ($entity) {
            return $entity->name . ' ' . $entity->lastname
                . ' (M.P: ' . $entity->license
                . ' - M.N:' . $entity->licenseNational . ')';
        });
        $clinicalHistory = $this->Reports->find()
            ->where([
                'Reports.patient_id' => $report->patient_id,
                'Reports.id IS NOT' => $report->id,
            ])
            ->contain([
                'Files',
                'FilesAuditor',
                'Modes',
                'Privatedoctors',
            ]);
        $modes = $this->Reports->Modes->find()->all()->combine('id', 'name');

        $this->set(compact(
            'report',
            'getStatuses',
            'doctors',
            'licenses',
            'companies',
            'modes',
            'privateDoctors',
            'clinicalHistory'
        ));
    }

    public function view($id)
    {
        try {
            $report = $this->Reports->get($id, [
                'contain' => [
                    'Patients' => 'Companies',
                    'Files',
                    'FilesAuditor',
                    'Modes',
                    'Privatedoctors',
                ],
            ]);
            if (empty($report)) {
                throw new RecordNotFoundException('No se encontro el ID.');
            }

            if (
                in_array($report->status, $this->Reports->getActiveStatuses())
                && $report->doctor_id == $this->Authentication->getIdentity()->id
            ) {
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
        $getStatuses = $this->Reports->getStatusForDoctor();
        $this->set(compact('report', 'getStatuses'));
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
            $report = $this->Reports->get($id, [
                'contain' => ['doctor', 'Patients' => ['Companies', 'Cities']],
            ]);
            if (!in_array($report->status, $this->Reports->getActiveStatuses())) {
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
}
