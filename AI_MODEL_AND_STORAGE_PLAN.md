# ClinObserve AI and Storage Planning Document

This document explains:
- which AI models fit this project,
- the approximate monthly AI usage cost in Indian Rupees (INR), and
- how much data a 2 GB MySQL database can hold in a clinical academic system like ClinObserve.

The goal is to help a professor understand the project in simple business terms, not just technical terms.

---

## 1) Which AI models suit this project

This system reviews academic clinical observations. It is not doing full diagnosis or treatment advice. It is generating educational feedback, safety notes, and structured review content.

For this use case, accuracy matters, but cost control matters too.

### Recommended model choices

| Model | Best for | Cost level | Recommended for this project? | Notes |
|---|---|---:|---|---|
| GPT-4o mini | Daily academic review, educational feedback, low cost | Low | Yes, best starting choice | Best balance of price and quality |
| GPT-4.1 mini | More accurate academic writing, better reasoning | Medium | Yes, best premium option | Good if you want stronger review quality |
| GPT-4o | High-quality review and broader reasoning | Higher | Only if needed | More expensive, usually not necessary for this project |
| GPT-4.1 | Maximum quality | Highest | Not usually needed | Too expensive for routine classroom use |

### Best choice for this project

For a medical education application, the most practical choice is:

1. Start with GPT-4o mini for a low-cost production setup.
2. Use GPT-4.1 mini if the department wants stronger academic reasoning and can afford a higher monthly cost.
3. Avoid GPT-4o or GPT-4.1 unless the project has very heavy usage or a strict academic-quality requirement.

### Why this is a good fit

The AI review in this project is designed to:
- summarize feedback,
- point out missing information,
- suggest learning topics,
- provide safe educational notes,
- avoid diagnosis or treatment instructions.

This is a structured educational-use case, not a high-risk medical advisory system. That means smaller, cheaper models are usually enough.

---

## 2) Cost estimate in INR

These are approximate values for planning only. Actual cost depends on the model you select and how many reviews are generated per month.

### Typical monthly cost by model

| Model | Approx. cost per review | Approx. monthly cost for 100 reviews | Approx. monthly cost for 500 reviews | Approx. monthly cost for 1,000 reviews |
|---|---:|---:|---:|---:|
| GPT-4o mini | ₹1 to ₹5 | ₹100 to ₹500 | ₹500 to ₹2,500 | ₹1,000 to ₹5,000 |
| GPT-4.1 mini | ₹3 to ₹12 | ₹300 to ₹1,200 | ₹1,500 to ₹6,000 | ₹3,000 to ₹12,000 |
| GPT-4o | ₹10 to ₹30 | ₹1,000 to ₹3,000 | ₹5,000 to ₹15,000 | ₹10,000 to ₹30,000 |
| GPT-4.1 | ₹15 to ₹40 | ₹1,500 to ₹4,000 | ₹7,500 to ₹20,000 | ₹15,000 to ₹40,000 |

### Practical estimate for this project

For an academic institution, a realistic budget is usually:

- Small use: ₹500 to ₹3,000 per month
- Normal use: ₹2,000 to ₹8,000 per month
- High use: ₹10,000 to ₹25,000 per month

### Best budget recommendation

If this is for a teaching department or clinical training program, the recommended setup is:

- Use GPT-4o mini
- Budget around ₹3,000 to ₹8,000 per month for normal usage
- Keep AI_ENABLED=true only when the department is ready to use it

This is a healthy and realistic operating budget for a training-focused system.

---

## 3) How much data can fit in a 2 GB MySQL database?

This is an important question for professors and administrators.

### Important fact

In this project, the database mainly stores:
- user information,
- student and professor profiles,
- patient case records,
- encounter details,
- review records,
- file metadata and storage paths.

The actual image files are usually stored in the application storage folder, not entirely inside MySQL. The database stores the file path, file size, MIME type, and related metadata.

This means the database is not filled mostly by photos unless the system is changed to store binary image files directly in the database.

---

## 4) Storage estimate by type of data

### A. Users and profiles

A user record is generally small. Each user plus profile data may be roughly:

- 1 KB to 5 KB per user in a normal application

This means a 2 GB database can hold a large number of users, depending on other data.

Practical estimate:

- 10,000 users: very comfortable
- 50,000 users: still possible in a normal academic system
- 100,000+ users: possible, but only if the system remains simple and text-heavy

For this project, a real-world target is:

- 5,000 to 30,000 users is a healthy range for a 2 GB setup

### B. Patient cases

Patient case records contain text fields like history, diagnosis, symptoms, notes, and examinations. These are much larger than users.

A patient case can be roughly:

- 5 KB to 50 KB depending on how much clinical detail is stored

Practical estimate:

- 5,000 cases: very comfortable
- 20,000 to 50,000 cases: still manageable in a 2 GB system
- 100,000+ cases: possible, but the database would grow quickly and search performance may slow down

A realistic academic use case is:

- 5,000 to 25,000 patient cases is a good range

### C. Encounters and student observations

Observation records are also text-heavy. Each encounter includes summary and notes.

A single encounter may take:

- 2 KB to 15 KB or more, depending on notes content

Practical estimate:

- 20,000 encounters: comfortable
- 100,000 encounters: possible
- 300,000+ encounters: can become heavy depending on text size and indexing

For an academic system, a realistic target is:

- 20,000 to 150,000 encounters

### D. AI review entries

AI review entries store:
- request metadata,
- model name,
- input snapshot,
- structured response JSON,
- error details.

These records can be moderate in size.

A single AI review may be roughly:

- 5 KB to 25 KB depending on response size

Practical estimate:

- 5,000 AI reviews: comfortable
- 20,000 to 50,000 AI reviews: still manageable
- 100,000+ AI reviews: possible, but storage usage rises quickly

### E. Photos / image metadata

This application stores image metadata in MySQL and the image files in storage.

A photo entry may be small in the database, but the actual image file may be large on disk.

Example:

- photo metadata row: small
- actual image file: 200 KB to 2 MB each depending on quality

This means:

- database size is not dominated by the image metadata,
- disk space is the bigger issue for actual image files,
- a 2 GB database can still handle a large number of photo records, but the server disk may become the real bottleneck

---

## 5) Simple planning table for a 2 GB MySQL database

Below is a practical estimate for this project.

| Item | Small system | Medium system | Large system |
|---|---:|---:|---:|
| Users | 1,000–5,000 | 5,000–20,000 | 20,000–50,000 |
| Staff / professors | 20–200 | 200–1,000 | 1,000–3,000 |
| Students | 500–3,000 | 3,000–10,000 | 10,000–30,000 |
| Patient cases | 500–3,000 | 3,000–20,000 | 20,000–50,000 |
| Encounters / observations | 2,000–20,000 | 20,000–100,000 | 100,000–300,000 |
| AI review records | 500–5,000 | 5,000–20,000 | 20,000–50,000 |
| Photos | 500–5,000 | 5,000–20,000 | 20,000–50,000 |

### Interpretation

For this project, a 2 GB database is enough for:
- a college or university teaching system,
- a few hundred to a few thousand active users,
- many patient cases and observations,
- regular AI-assisted review,
- moderate image usage.

It is not designed for huge public-scale healthcare data systems with tens of thousands of images and millions of records.

---

## 6) Realistic capacity for this project

For ClinObserve, the most realistic business-level capacity is:

- 5,000 to 20,000 total users
- 3,000 to 20,000 patient cases
- 20,000 to 100,000 encounters
- 5,000 to 20,000 AI review records
- 5,000 to 20,000 photos with normal image sizes

This is a good, practical range for a 2 GB MySQL setup used in department-level academic training.

---

## 7) What can make the database fill quickly?

The database grows faster when:
- many large clinical notes are stored,
- a large number of AI reviews are saved with JSON responses,
- images are stored directly in the database instead of only on disk,
- old records are never cleaned,
- many files and logs are kept without review.

---

## 8) Final recommendation

### For AI

Use:
- GPT-4o mini as the default
- GPT-4.1 mini if stronger academic reasoning is needed

Budget:
- ₹3,000 to ₹8,000 per month for normal department use

### For storage

A 2 GB MySQL database is adequate for a teaching / academic deployment with moderate records and moderate review activity.

This project is well-suited for:
- university departments,
- academic clinical training programs,
- faculty review workflows,
- controlled student case records,
- limited but meaningful AI support.

It is not ideal for a very large public hospital or national-scale medical data platform.

---

## 9) Short summary for a professor

- AI support is already built into the project.
- The best model for this project is GPT-4o mini, with GPT-4.1 mini as a stronger but costlier option.
- Expected AI cost in INR is usually low to moderate, around ₹3,000 to ₹8,000 per month in normal use.
- A 2 GB MySQL database is enough for a mid-size academic system.
- A realistic system size would be thousands of users, thousands of cases, tens of thousands of encounters, and moderate AI and photo usage.
- The project is designed for department-level academic use, not very large production healthcare scale.

This project is practical, manageable, and scalable for academic clinical education when the number of records stays within these ranges.
