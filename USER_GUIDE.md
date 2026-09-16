# ClinObserve User Guide

This guide explains how students and HODs use ClinObserve in day-to-day academic workflow.

## 1. Overview

ClinObserve is a department-based clinical learning system. It gives students a place to:

- review patient cases
- record observations
- upload clinical images
- track case activity
- view departmental feedback

It gives HODs a place to:

- manage student accounts
- create and maintain cases
- review student observations
- write private faculty feedback
- monitor department performance and reports

The system is role-based. Students can only see the data they are allowed to access, while HODs can view department-wide records and manage student accounts.

---

## 2. Access and login

### Student login
1. Open the application login page.
2. Enter your assigned email and password.
3. If your account is newly created, you will be required to change your password before continuing.
4. After login, you are taken to your dashboard.

### HOD / HOS login
1. Sign in with the HOD account.
2. You will be directed to the HOD dashboard.
3. From here you can manage students and review learner activity.

### Password recovery
- Students may use the password reset flow if email recovery is enabled.
- If email recovery is disabled, the user must follow the HOD process for credential reset.

---

## 3. Student user guide

### Student dashboard
The student dashboard gives an overview of:

- recent activity
- own encounters and counts
- patient case activity
- calendar activity
- recent feedback entries

From the dashboard, you can navigate to:

- My observations
- Patients
- Calendar
- Profile

### Profile management
Students can update personal, non-administrative information in their profile, such as:

- phone number
- date of birth
- gender
- address
- short bio
- profile image

Students cannot change administrative details like role, academic registration details, or other HOD-controlled data.

### Viewing patient cases
1. Go to Patients.
2. Use search to find a patient by name or case number.
3. Open a case to view the case timeline and all linked observations.
4. You can review the clinical context and the observation history for that patient.

### Recording an observation
1. Open the patient case.
2. Click Record Encounter.
3. Fill in the required observation fields such as summary, symptoms, examination findings, assessment, and learning notes.
4. Upload up to five images if needed.
5. Save the observation.

### Editing an observation
Students can edit their own observations only while the encounter is still unlocked.

A record becomes locked after certain protected actions such as:

- the first AI review request
- first faculty review

If the encounter is locked, a new encounter should be created for later follow-up instead of editing the old one.

### Image management
For a student-owned unlocked observation:

- add images
- remove images
- view images that are authorized for shared access

Images are private and are not open to the public internet.

### AI review flow
If AI review is enabled, a student may request a text-only educational review.

Workflow:

1. Open the encounter.
2. Choose AI review.
3. Review the de-identified text preview.
4. Confirm the content is safe to send.
5. Submit the request.
6. Wait for the generated educational feedback.

The AI review is intended only as educational support and must not be treated as medical advice.

### Calendar view
The calendar shows:

- monthly encounters
- patient dates
- student learning activity
- selected-day details

Students can only see their own calendar activity, while HODs may view broader departmental calendars.

### Feedback access
Students can see faculty feedback related to their own encounters, but they cannot see private feedback on other students’ work.

---

## 4. HOD / HOS user guide

### HOD dashboard
The HOD dashboard is the main operational hub. It includes summary information such as:

- department activity
- number of students active recently
- recent encounters
- recent AI reviews
- students without faculty feedback
- monthly and date-based summaries

### Managing students
From the Students section, the HOD can:

- view all students
- search by name, email, or roll number
- create new student accounts
- edit student profile and academic data
- activate or deactivate accounts
- reset temporary passwords and revoke sessions

When creating a student, the HOD provides the required learner details and the initial credential is shared securely.

The student must change their password on first login.

### Managing patient cases
The HOD can:

- browse all patient cases
- create new patient records
- edit case context when permitted
- review the timeline of observations tied to a case

A case may be edited by the original creator before it has any linked encounters. After an encounter is recorded, the case becomes shared and HOD-level control is required for changes.

### Reviewing student observations
The HOD can view any student’s observation records and the patient case timeline.

This includes:

- encounter details
- attached images
- student notes
- evaluation history

HODs can review clinical reasoning and academic documentation without rewriting the student’s content.

### Writing faculty feedback
HODs may add private feedback to an encounter.

This feedback is:

- visible to the student only when it belongs to their encounter
- visible to the HOD who created it
- hidden from unrelated students

If the HOD authored the feedback, they may update their own review record later, while preserving the original review history.

### Reports and summaries
The HOD can use reports to review:

- monthly attendance activity
- number of encounters per student
- patient counts
- student participation trends
- department-wide activity ranges

These reports help identify engagement, coverage, and progress across the academic cycle.

### Department calendar
The HOD calendar can be filtered to:

- all students
- a selected student
- a selected month and date

This allows the HOD to see a department-wide view for learning activity, while keeping student-specific access restricted appropriately.

---

## 5. Typical workflow examples

### Student workflow
1. Log in.
2. Open the patient list.
3. Select a case.
4. Record an encounter.
5. Upload images if required.
6. Save and review the observation.
7. Wait for or review faculty feedback.
8. If enabled, submit an AI review for educational support.

### HOD workflow
1. Log in to the HOD dashboard.
2. Create or manage student accounts.
3. Review the student list and academic activity.
4. Open a student encounter and assess the record.
5. Add private feedback.
6. Review dashboards and reports for department performance.

---

## 6. Security and access expectations

ClinObserve enforces role restrictions and authorization checks.

Important rules:

- Students cannot access another student’s private reviews.
- Students cannot edit another student’s records.
- HODs cannot impersonate students when creating observations.
- HODs are the only users permitted to manage student credentials.
- Private review and image content remains restricted to authorized viewers.

---

## 7. Responsibilities of users

### Students
- keep your login secure
- do not share patient images or private feedback externally
- confirm that content is de-identified before sending to AI
- use the platform only for learning and academic review

### HODs
- manage accounts responsibly
- ensure students are active and properly assigned
- supervise faculty feedback
- verify AI review safety and institutional compliance

---

## 8. Best practices

- Use meaningful, academic reflections in encounter notes.
- Keep case documentation de-identified and professional.
- Only submit AI review requests for text that has been checked for identifying details.
- Use the calendar and reports as regular academic monitoring tools.
- Do not treat AI-generated feedback as a final diagnosis or treatment recommendation.

---

## 9. Troubleshooting

### Cannot log in
- Check your email and password.
- Make sure your account is active.
- If this is your first login, complete the required password change.
- Contact the HOD if you have not received your account details.

### Cannot edit an encounter
- The encounter may be locked after AI review or faculty review.
- Only the owner can edit an unlocked observation.

### No patient appears in search
- Check if the patient case is active and visible.
- Search by case number or a different name variation.

### Missing feedback
- Ensure the observation was reviewed by the HOD.
- Only authorized users can access private feedback.

---

## 10. Support

For operational questions, contact the department administrator or HOD responsible for the system.

For technical setup and deployment details, refer to [README.md](README.md) and [PROJECT_PLAN.md](PROJECT_PLAN.md).
