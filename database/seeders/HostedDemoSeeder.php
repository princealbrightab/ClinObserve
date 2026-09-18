<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HostedDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! config('browser-maintenance.allow_demo_seed')) {
            throw ValidationException::withMessages(['demo' => 'Set BROWSER_MAINTENANCE_ALLOW_DEMO_SEED=true before adding hosted demo data.']);
        }

        DB::transaction(function (): void {
            $hod = User::where('email', 'hod@clinobserve.test')->first()
                ?? User::where('role', UserRole::Hod)->where('is_active', true)->orderBy('id')->first();
            if (! $hod) {
                $hod = User::create([
                    'name' => 'HOD Admin',
                    'email' => 'hod@clinobserve.test',
                    'password' => Hash::make('Hod@12345'),
                ]);
                $hod->role = UserRole::Hod;
                $hod->is_active = true;
                $hod->must_change_password = false;
                $hod->save();
                $hod = $hod->fresh();
            }

            $professor = User::where('email', 'professor@clinobserve.test')->first();
            if (! $professor) {
                $professor = $this->createAccount('Professor Admin', 'professor@clinobserve.test', 'Professor@12345', UserRole::Professor, $hod);
            }
            $professor->role = UserRole::Professor;
            $professor->created_by = $hod->id;
            $professor->is_active = true;
            $professor->must_change_password = false;
            $professor->save();

            $student = User::where('email', 'student@clinobserve.test')->first();
            if (! $student) {
                $student = $this->createAccount('Student User', 'student@clinobserve.test', 'Student@12345', UserRole::Student, $hod);
            }
            $student->role = UserRole::Student;
            $student->created_by = $hod->id;
            $student->is_active = true;
            $student->must_change_password = false;
            $student->professor_id = $professor->id;
            $student->save();
            if (! $student->studentProfile()->exists()) {
                $student->studentProfile()->create([
                    'roll_number' => 'HOSTED-DEMO-001',
                    'batch' => 'Demo Batch 2026',
                    'academic_year' => '2026-2027',
                    'college' => 'Demo Medical College',
                    'course' => 'MBBS',
                    'department' => 'General Medicine',
                    'bio' => 'Demo student using a realistic hosted case for assessment practice.',
                ]);
            }

            $cases = [
                [
                    'display_name' => 'Amit',
                    'gender' => 'male',
                    'age_years' => 32,
                    'admission_date' => now()->subDays(4)->toDateString(),
                    'condition' => 'Acute asthma exacerbation',
                    'chief_complaint' => 'Shortness of breath and wheezing for the past 12 hours.',
                    'presenting_symptoms' => 'Progressive dyspnoea, chest tightness, wheeze, and intermittent cough. Symptoms worsened after exposure to dust and smoke at home.',
                    'history_of_present_illness' => 'The patient reports increasing dyspnoea, chest tightness, and audible wheeze after exposure to dust at home. He used an inhaler twice without complete relief. He denies fever, orthopnoea, or leg swelling.',
                    'past_medical_history' => 'Known asthma since childhood. Occasional use of salbutamol inhaler. No prior hospital admissions.',
                    'family_history' => 'Father has hypertension. No family history of asthma or chronic lung disease.',
                    'medication_history' => 'Salbutamol inhaler as needed, no regular controller medication.',
                    'allergy_history' => 'No known drug allergies.',
                    'social_history' => 'Works as a carpenter. Exposed to dust daily at work and home.',
                    'examination_findings' => 'Mild accessory muscle use, respiratory rate 28/min, oxygen saturation 92% on room air, diffuse expiratory wheeze bilaterally, no cyanosis.',
                    'working_diagnosis' => 'Acute asthma exacerbation with mild hypoxia.',
                    'confirmed_diagnosis' => 'Acute exacerbation of bronchial asthma',
                    'investigations' => 'Pulse oximetry, chest auscultation, peak flow assessment, blood gases if indicated.',
                    'management_notes' => 'Nebulised bronchodilator therapy and reassessment of response to treatment.',
                    'clinical_notes' => 'Patient is alert but anxious; no fever or lower limb oedema. Wheezing is diffuse and intermittent. Reassurance and inhaler review discussed.',
                    'additional_context' => [
                        'triage_score' => 'Category 2',
                        'oxygen_requirement' => '2 L/min via nasal cannula',
                        'risk_factors' => ['dust exposure', 'occupational irritant exposure', 'poor inhaler adherence'],
                        'vitals' => ['SpO2' => '92%', 'RR' => '28/min', 'HR' => '112/min'],
                        'disposition' => 'Observation unit for repeated bronchodilator therapy',
                    ],
                ],
                [
                    'display_name' => 'Riya',
                    'gender' => 'female',
                    'age_years' => 28,
                    'admission_date' => now()->subDays(7)->toDateString(),
                    'condition' => 'Lower respiratory tract infection',
                    'chief_complaint' => 'Fever and productive cough for 4 days.',
                    'presenting_symptoms' => 'High-grade fever, cough with yellow sputum, fatigue, and mild pleuritic chest pain.',
                    'history_of_present_illness' => 'The patient developed fever and cough 4 days ago, with increased sputum production and dyspnoea on exertion. No haemoptysis or notable travel history.',
                    'past_medical_history' => 'No chronic illness. No previous lung disease.',
                    'family_history' => 'No relevant family history.',
                    'medication_history' => 'No regular medication; took paracetamol intermittently.',
                    'allergy_history' => 'No known allergies.',
                    'social_history' => 'Works in a clinic admin role, no smoking history.',
                    'examination_findings' => 'Temperature 38.6°C, pulse 104/min, respiratory rate 22/min, crackles in right lower zone, dullness on percussion noted.',
                    'working_diagnosis' => 'Community-acquired pneumonia with lower lobe consolidation.',
                    'confirmed_diagnosis' => 'Community-acquired pneumonia',
                    'investigations' => 'Chest X-ray, complete blood count, CRP, sputum culture if required.',
                    'management_notes' => 'Assess oxygen requirement, start empirical antibiotics if clinically indicated, and monitor fever trend.',
                    'clinical_notes' => 'Patient has focal chest tenderness and cough with purulent sputum. No haemoptysis or pleuritic rub detected.',
                    'additional_context' => [
                        'triage_score' => 'Category 3',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['exposure to clinic patients', 'recent upper respiratory infection'],
                        'vitals' => ['Temp' => '38.6°C', 'RR' => '22/min', 'HR' => '104/min'],
                        'disposition' => 'Medical ward with serial observations',
                    ],
                ],
                [
                    'display_name' => 'Sanjay Kumar',
                    'gender' => 'male',
                    'age_years' => 56,
                    'admission_date' => now()->subDays(11)->toDateString(),
                    'condition' => 'Type 2 diabetes with poor glycaemic control',
                    'chief_complaint' => 'Excessive thirst and frequent urination for 2 weeks.',
                    'presenting_symptoms' => 'Polydipsia, polyuria, fatigue, recurrent infections, and slow wound healing.',
                    'history_of_present_illness' => 'The patient reports worsening thirst and frequency of urination over the past two weeks. He has experienced fatigue and recurrent skin infections, with a weight loss of approximately 3 kg.',
                    'past_medical_history' => 'Known type 2 diabetes for 8 years, poor adherence to medications, hypertension.',
                    'family_history' => 'Mother has diabetes mellitus.',
                    'medication_history' => 'Metformin, antihypertensive therapy, inconsistent adherence to schedule.',
                    'allergy_history' => 'No known drug allergies.',
                    'social_history' => 'Sedentary lifestyle, occasional alcohol use, no smoking.',
                    'examination_findings' => 'BMI 31, mild dehydration, fasting glucose elevated, blood pressure 148/92 mmHg, no acute distress.',
                    'working_diagnosis' => 'Poorly controlled type 2 diabetes with possible metabolic decompensation.',
                    'confirmed_diagnosis' => 'Poorly controlled type 2 diabetes mellitus',
                    'investigations' => 'HbA1c, fasting blood glucose, renal function, urine dipstick, screening for complications.',
                    'management_notes' => 'Review medication adherence, optimise diabetes regimen, and check for complications such as nephropathy or infection.',
                    'clinical_notes' => 'Patient reports reduced adherence to metformin and poor dietary control. Skin infections are recurrent, but there is no acute infection at presentation.',
                    'additional_context' => [
                        'triage_score' => 'Category 3',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['obesity', 'poor medication adherence', 'hypertension'],
                        'vitals' => ['BP' => '148/92 mmHg', 'BMI' => '31', 'HR' => '88/min'],
                        'disposition' => 'Diabetes education and medication optimisation follow-up',
                    ],
                ],
                [
                    'display_name' => 'Fatima Ali',
                    'gender' => 'female',
                    'age_years' => 41,
                    'admission_date' => now()->subDays(6)->toDateString(),
                    'condition' => 'Acute gastroenteritis',
                    'chief_complaint' => 'Vomiting and diarrhoea for 2 days.',
                    'presenting_symptoms' => 'Frequent vomiting, loose stools, abdominal cramps, and general weakness.',
                    'history_of_present_illness' => 'The patient developed vomiting and watery diarrhoea after eating roadside food. She reports mild fever and excessive thirst since yesterday.',
                    'past_medical_history' => 'No chronic illnesses. Had occasional reflux symptoms.',
                    'family_history' => 'No relevant family history.',
                    'medication_history' => 'No chronic medications.',
                    'allergy_history' => 'No known drug allergies.',
                    'social_history' => 'Works as a school teacher. Lives with family members, no travel history.',
                    'examination_findings' => 'Dry tongue, tachycardia, mild dehydration, soft abdomen with diffuse tenderness.',
                    'working_diagnosis' => 'Acute infectious gastroenteritis with dehydration.',
                    'confirmed_diagnosis' => 'Acute gastroenteritis',
                    'investigations' => 'Electrolytes, stool culture if severe, hydration status assessment.',
                    'management_notes' => 'Oral rehydration therapy, monitor urine output, review for severe dehydration signs.',
                    'clinical_notes' => 'Symptoms are consistent with dehydration from gastroenteritis; no blood in stool or severe abdominal guarding noted.',
                    'additional_context' => [
                        'triage_score' => 'Category 3',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['food exposure', 'dehydration risk'],
                        'vitals' => ['HR' => '104/min', 'BP' => '100/68 mmHg', 'Temp' => '37.8°C'],
                        'disposition' => 'Observation for rehydration and discharge education',
                    ],
                ],
                [
                    'display_name' => 'Rahul',
                    'gender' => 'male',
                    'age_years' => 58,
                    'admission_date' => now()->subDays(9)->toDateString(),
                    'condition' => 'Acute myocardial infarction',
                    'chief_complaint' => 'Chest pain and sweating for 3 hours.',
                    'presenting_symptoms' => 'Central chest pressure with radiation to the left arm, nausea, diaphoresis, and shortness of breath.',
                    'history_of_present_illness' => 'The patient developed sudden central chest discomfort while walking. Pain is severe and persistent, associated with sweating and nausea.',
                    'past_medical_history' => 'Hypertension, hyperlipidaemia, smoker for 30 years.',
                    'family_history' => 'Father died of heart disease at age 62.',
                    'medication_history' => 'Amlodipine, atorvastatin, occasional NSAIDs.',
                    'allergy_history' => 'Penicillin allergy.',
                    'social_history' => 'Smoker, sedentary work pattern, regular alcohol intake.',
                    'examination_findings' => 'Pale and sweating, blood pressure 96/62, tachycardic, mild crackles at bases.',
                    'working_diagnosis' => 'Acute coronary syndrome with probable myocardial infarction.',
                    'confirmed_diagnosis' => 'Acute myocardial infarction',
                    'investigations' => 'ECG, troponin, chest X-ray, serial cardiac enzymes.',
                    'management_notes' => 'Urgent cardiac assessment, monitoring, aspirin protocol as clinically appropriate, and risk stratification.',
                    'clinical_notes' => 'Pain characteristics and ECG findings are highly suggestive of acute coronary event. Immediate monitoring is required.',
                    'additional_context' => [
                        'triage_score' => 'Category 1',
                        'oxygen_requirement' => '2 L/min if needed',
                        'risk_factors' => ['smoking', 'hypertension', 'hyperlipidaemia'],
                        'vitals' => ['BP' => '96/62 mmHg', 'HR' => '118/min', 'SpO2' => '94%'],
                        'disposition' => 'Coronary care monitoring',
                    ],
                ],
                [
                    'display_name' => 'Ananya Bose',
                    'gender' => 'female',
                    'age_years' => 24,
                    'admission_date' => now()->subDays(5)->toDateString(),
                    'condition' => 'Acute appendicitis',
                    'chief_complaint' => 'Right lower abdominal pain for 1 day.',
                    'presenting_symptoms' => 'Pain migrated from periumbilical region to right iliac fossa, nausea, anorexia, low-grade fever.',
                    'history_of_present_illness' => 'The patient developed vague periumbilical discomfort before localising to the right lower abdomen. She has had reduced appetite and intermittent nausea.',
                    'past_medical_history' => 'No major diseases, regular menstrual cycle.',
                    'family_history' => 'No significant family history.',
                    'medication_history' => 'No regular medication.',
                    'allergy_history' => 'No known allergies.',
                    'social_history' => 'Student, no smoking, no recent travel.',
                    'examination_findings' => 'Tenderness at McBurney point, guarding in right iliac fossa, mild fever, normal bowel sounds.',
                    'working_diagnosis' => 'Acute appendicitis.',
                    'confirmed_diagnosis' => 'Acute appendicitis',
                    'investigations' => 'CBC, abdominal ultrasound, surgical review.',
                    'management_notes' => 'Urgent surgical review and perioperative assessment. Monitor for perforation or peritonitis.',
                    'clinical_notes' => 'Pain migration and right iliac fossa tenderness are classic for appendicitis. No evidence of urinary symptoms or gynaecological pathology noted.',
                    'additional_context' => [
                        'triage_score' => 'Category 2',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['age group', 'appendiceal pathology'],
                        'vitals' => ['Temp' => '37.9°C', 'HR' => '96/min', 'BP' => '110/72 mmHg'],
                        'disposition' => 'Surgical evaluation',
                    ],
                ],
                [
                    'display_name' => 'Nisha',
                    'gender' => 'female',
                    'age_years' => 36,
                    'admission_date' => now()->subDays(3)->toDateString(),
                    'condition' => 'Urinary tract infection',
                    'chief_complaint' => 'Burning micturition and lower abdominal discomfort.',
                    'presenting_symptoms' => 'Dysuria, urgency, frequency, suprapubic pain, and mild fever.',
                    'history_of_present_illness' => 'The patient reports worsening urinary burning and frequency over the last 48 hours. She has no flank pain or vomiting.',
                    'past_medical_history' => 'Occasional recurrent UTIs, otherwise well.',
                    'family_history' => 'Mother had recurrent urinary infections.',
                    'medication_history' => 'No chronic medication use.',
                    'allergy_history' => 'No known allergies.',
                    'social_history' => 'Works in retail. No significant risk factors.',
                    'examination_findings' => 'Suprapubic tenderness, no costovertebral angle tenderness, afebrile to mild fever.',
                    'working_diagnosis' => 'Lower urinary tract infection.',
                    'confirmed_diagnosis' => 'Acute uncomplicated urinary tract infection',
                    'investigations' => 'Urinalysis, urine culture, urine nitrites and leukocytes.',
                    'management_notes' => 'Start empiric therapy based on local antibiogram and advise hydration and review of symptoms.',
                    'clinical_notes' => 'No signs of kidney involvement or systemic sepsis; symptoms are limited to lower urinary tract.',
                    'additional_context' => [
                        'triage_score' => 'Category 3',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['recurrent UTI'],
                        'vitals' => ['Temp' => '37.7°C', 'HR' => '86/min', 'BP' => '118/74 mmHg'],
                        'disposition' => 'Outpatient care with follow-up',
                    ],
                ],
                [
                    'display_name' => 'Vinod',
                    'gender' => 'male',
                    'age_years' => 64,
                    'admission_date' => now()->subDays(10)->toDateString(),
                    'condition' => 'Stroke with hemiparesis',
                    'chief_complaint' => 'Sudden weakness on the right side and slurred speech.',
                    'presenting_symptoms' => 'Facial droop, right arm weakness, dysarthria, and difficulty with comprehension.',
                    'history_of_present_illness' => 'The patient woke with sudden right-sided weakness and speech difficulty. Symptoms were abrupt and not preceded by trauma.',
                    'past_medical_history' => 'Hypertension, atrial fibrillation on treatment.',
                    'family_history' => 'No relevant family history.',
                    'medication_history' => 'Warfarin, antihypertensive therapy.',
                    'allergy_history' => 'No known allergies.',
                    'social_history' => 'Retired teacher, no smoking, non-drinker.',
                    'examination_findings' => 'Right facial weakness, right arm drift, slurred speech, mild dysphasia.',
                    'working_diagnosis' => 'Acute ischaemic stroke.',
                    'confirmed_diagnosis' => 'Acute ischaemic stroke',
                    'investigations' => 'CT head, NIHSS assessment, blood tests, carotid evaluation as needed.',
                    'management_notes' => 'Urgent stroke pathway review, neurological observations, and thrombectomy consideration if indicated.',
                    'clinical_notes' => 'Time of onset is critical; neurological deficit is focal and consistent with cerebral infarction.',
                    'additional_context' => [
                        'triage_score' => 'Category 1',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['atrial fibrillation', 'hypertension'],
                        'vitals' => ['BP' => '142/88 mmHg', 'HR' => '96/min', 'SpO2' => '96%'],
                        'disposition' => 'Stroke unit admission',
                    ],
                ],
                [
                    'display_name' => 'Priya',
                    'gender' => 'female',
                    'age_years' => 46,
                    'admission_date' => now()->subDays(8)->toDateString(),
                    'condition' => 'Deep vein thrombosis',
                    'chief_complaint' => 'Pain and swelling in the left leg.',
                    'presenting_symptoms' => 'Left calf swelling, warmth, tenderness, and mild redness over 24 hours.',
                    'history_of_present_illness' => 'The patient developed unilateral calf pain and swelling after a long-haul flight. She reports no trauma or recent surgery.',
                    'past_medical_history' => 'Obesity, oral contraceptive use, occasional smoking.',
                    'family_history' => 'No known clotting disorder in family.',
                    'medication_history' => 'Combined oral contraceptive pill.',
                    'allergy_history' => 'No known allergies.',
                    'social_history' => 'Business traveller, sedentary during flights.',
                    'examination_findings' => 'Left calf larger than right, warm, erythematous, tender on palpation, no cyanosis.',
                    'working_diagnosis' => 'Deep venous thrombosis in lower limb.',
                    'confirmed_diagnosis' => 'Deep vein thrombosis',
                    'investigations' => 'D-dimer, duplex ultrasound, coagulation profile.',
                    'management_notes' => 'Assess bleeding risk; initiate anticoagulation therapy under clinician guidance and advise follow-up.',
                    'clinical_notes' => 'The presentation is consistent with a recent venous thromboembolism risk after prolonged immobility. No signs of PE at present.',
                    'additional_context' => [
                        'triage_score' => 'Category 2',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['long-haul travel', 'oral contraceptive use', 'obesity'],
                        'vitals' => ['BP' => '124/80 mmHg', 'HR' => '92/min', 'Temp' => '36.8°C'],
                        'disposition' => 'Anticoagulation review and discharge planning',
                    ],
                ],
                [
                    'display_name' => 'Kabir',
                    'gender' => 'male',
                    'age_years' => 19,
                    'admission_date' => now()->subDays(2)->toDateString(),
                    'condition' => 'Acute hepatitis A',
                    'chief_complaint' => 'Jaundice and fatigue for 3 days.',
                    'presenting_symptoms' => 'Malaise, nausea, dark urine, jaundice, and right upper quadrant discomfort.',
                    'history_of_present_illness' => 'The patient developed fatigue and mild fever followed by dark urine and yellowing of the sclera. No alcohol use reported.',
                    'past_medical_history' => 'No chronic disease; immunisations up to date.',
                    'family_history' => 'No relevant family history.',
                    'medication_history' => 'No regular medications.',
                    'allergy_history' => 'No known allergies.',
                    'social_history' => 'Student, recent travel and frequent eating out.',
                    'examination_findings' => 'Jaundice, tender hepatomegaly, normal vital signs except mild tachycardia.',
                    'working_diagnosis' => 'Acute viral hepatitis.',
                    'confirmed_diagnosis' => 'Acute hepatitis A infection',
                    'investigations' => 'LFTs, bilirubin, hepatitis serology, viral panel if indicated.',
                    'management_notes' => 'Supportive care, hydration, and infection control advice. Monitor liver function trend.',
                    'clinical_notes' => 'Clinical picture is suggestive of hepatocellular injury with jaundice; no history of alcohol or hepatotoxic drugs.',
                    'additional_context' => [
                        'triage_score' => 'Category 3',
                        'oxygen_requirement' => 'None',
                        'risk_factors' => ['travel', 'food exposure'],
                        'vitals' => ['Temp' => '37.6°C', 'HR' => '92/min', 'BP' => '116/72 mmHg'],
                        'disposition' => 'Observation and liver function review',
                    ],
                ],
            ];

            $existingPatientNames = Patient::whereIn('display_name', array_column($cases, 'display_name'))->pluck('display_name')->all();

            foreach ($cases as $case) {
                if (in_array($case['display_name'], $existingPatientNames, true)) {
                    continue;
                }

                $patient = new Patient([
                    'display_name' => $case['display_name'],
                    'data_classification' => 'synthetic',
                    'gender' => $case['gender'],
                    'age_years' => $case['age_years'],
                    'admission_date' => $case['admission_date'],
                    'condition' => $case['condition'],
                    'chief_complaint' => $case['chief_complaint'],
                    'presenting_symptoms' => $case['presenting_symptoms'],
                    'history_of_present_illness' => $case['history_of_present_illness'],
                    'past_medical_history' => $case['past_medical_history'],
                    'family_history' => $case['family_history'],
                    'medication_history' => $case['medication_history'],
                    'allergy_history' => $case['allergy_history'],
                    'social_history' => $case['social_history'],
                    'examination_findings' => $case['examination_findings'],
                    'working_diagnosis' => $case['working_diagnosis'],
                    'confirmed_diagnosis' => $case['confirmed_diagnosis'],
                    'investigations' => $case['investigations'],
                    'management_notes' => $case['management_notes'],
                    'clinical_notes' => $case['clinical_notes'],
                    'additional_context' => $case['additional_context'],
                ]);
                $patient->created_by = $hod->id;
                $patient->updated_by = $hod->id;
                $patient->save();
            }

            $this->command?->info('Created 1 demo professor, 1 assigned student, and 10 realistic synthetic patient cases.');
        });
    }

    private function createAccount(string $name, string $email, string $password, UserRole $role, ?User $hod = null): User
    {
        $user = new User(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $user->role = $role;
        if ($hod !== null) {
            $user->created_by = $hod->id;
        }
        $user->is_active = true;
        $user->must_change_password = $role !== UserRole::Hod;
        $user->save();

        return $user->fresh();
    }
}
