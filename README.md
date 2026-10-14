# Hospital Information System (HIS)
### Enterprise Clinical Management & Hospital Operations Platform
**Built with Laravel 13 &bull; PostgreSQL 18 &bull; Bootstrap 5.3 &bull; Bootstrap Icons &bull; Air-Gapped Intranet Ready**

---

## 1. Executive Summary & Overview

**Hospital Information System (HIS)** is a production-ready, clinical-grade hospital information and management web application designed for hospitals, clinics, medical centers, and tertiary healthcare networks. 

Engineered with the latest **Laravel 13** and architected for **PostgreSQL 18**, this system delivers end-to-end management of patient care, clinical documentation, inpatient hospitalization, pharmacy dispensaries, diagnostic laboratories, and revenue cycle billing.

### Key Architectural Highlights:
- **100% Air-Gapped & Internal Network Ready**: All frontend libraries (Bootstrap 5.3.8, Bootstrap Icons 1.13.2, custom stylesheets, and Web Open Font formats `.woff2`/`.woff`) are downloaded directly into `public/vendor/`. The application operates flawlessly on isolated hospital local area networks (LAN) without internet connectivity.
- **Enterprise PostgreSQL 18 Architecture**: Full schema utilizing PostgreSQL native `JSONB` for allergies/chronic illnesses/available days, high-precision `NUMERIC(12, 2)` for accounting and formulary pricing, relational foreign key constraints, and indexed audit logs.
- **Dedicated Admin Bulk Data Import Hub**: Complete automated CSV import engine for Patients, Doctors, Medicines, Lab Catalogs, and Clinical Departments, including downloadable pre-formatted CSV templates and granular row-by-row error logging.
- **Role-Based Access Control (RBAC)**: Tailored workflows and route protection for Hospital Administrators, Attending Physicians, Registered Nurses, Clinical Pharmacists, Medical Laboratory Technologists, Front Desk Receptionists, and Billing Accountants.

---

## 2. Core Hospital Modules

```
                           +-----------------------------------------------+
                           |    Hospital Information System Core Engine    |
                           +-----------------------------------------------+
                                             |
     +-----------------+---------------------+--------------------+-----------------+
     |                 |                     |                    |                 |
+----+----+      +-----+-----+         +-----+-----+        +-----+-----+     +-----+-----+
|   OPD   |      |    EMR    |         |    IPD    |        |  Pharmacy |     |    LIS    |
| Outpt.  |      | Clinical  |         | Inpatient |        | Formulary |     | Diagnostic|
| Booking |      | Records   |         | Wards/Beds|        | Dispensing|     | Pathology |
+----+----+      +-----+-----+         +-----+-----+        +-----+-----+     +-----+-----+
     |                 |                     |                    |                 |
     +-----------------+---------------------+--------------------+-----------------+
                                             |
                           +-----------------+------------------+
                           |  Billing & Revenue Cycle (Invoices)|
                           +-----------------+------------------+
                                             |
                           +-----------------+------------------+
                           |  Admin Bulk Import & Audit Trail   |
                           +------------------------------------+
```

### 1. Outpatient Department (OPD) & Appointments
- Multi-specialty scheduling queue with date, time, status, and urgency priority filters (`routine`, `urgent`, `emergency`).
- Patient check-in tracking and direct handoff to nursing triage or clinical consultation.

### 2. Electronic Medical Records (EMR / EHR)
- Complete patient master registry with sequential Medical Record Numbers (MRN format: `MRN-YYYY-XXXX`).
- Demographic details, National ID/SSN, Next-of-Kin Emergency Contacts, Blood Groups, Genotypes, and Insurance Carrier policies.
- Comprehensive EHR timeline aggregating clinical consultations, triage vitals, prescription orders, lab findings, and admission records.

### 3. Nursing Triage & Vital Signs
- Standardized 5-tier Emergency Triage classification:
  1. **Resuscitation** (Immediate threat)
  2. **Emergency** (Within 15 minutes)
  3. **Urgent** (Within 30 minutes)
  4. **Semi-Urgent** (Within 60 minutes)
  5. **Non-Urgent** (Standard outpatient queue)
- Comprehensive physiological measurement: Systolic/Diastolic BP (mmHg), Heart Rate (bpm), Body Temp (°C), Respiratory Rate, SpO2 (%), Weight (kg), Height (cm), and automated Body Mass Index (BMI) calculation.

### 4. Physician Consultation Workspace
- Clinical encounter recording with Chief Complaint, History of Present Illness (HPI), and Physical Examination findings.
- Primary diagnosis with **ICD-10 diagnostic coding** (e.g. `I10` Essential Hypertension, `E11.9` Diabetes Mellitus).
- Integrated Electronic Prescribing (direct formulary order) and Laboratory Test ordering within the same consultation screen.

### 5. Inpatient Department (IPD) & Bed Management
- Multi-ward facility modeling: Intensive Care Unit (ICU), General Medical Ward, Maternity Ward, Pediatrics Ward, Surgical Ward, and VIP Executive Suites.
- Real-time interactive bed occupancy visualization (Available, Occupied, Cleaning, Maintenance).
- Patient admission and discharge workflows with automated stay duration calculation, discharge condition summary, and auto-generated ward accommodation billing invoices.

### 6. Pharmacy & Drug Formulary Inventory
- Medication catalog tracking Brand Name, Generic Molecule, Pharmacological Category, Dosage Form, Strength, Unit Retail Price, and Acquisition Cost.
- Real-time stock level monitoring with automated **Low-Stock Warning triggers** and Out-of-Stock alerts.
- Batch/Lot numbers, storage conditions, and expiry date monitoring.
- Prescription fulfillment queue allowing pharmacists to dispense drugs, deduct stock automatically, and generate invoice items.

### 7. Laboratory Information System (LIS)
- Diagnostic pathology and radiology test catalog with turnaround times, sample specimen requirements, and reference ranges.
- Order management with sample collection timestamping.
- Laboratory technician finding entry interface with observed values, units, and clinical severity flags (`Normal`, `High`, `Low`, `Critical`).

### 8. Billing & Revenue Cycle Management (RCM)
- Automated fee aggregation from doctor consultations, inpatient bed nights, dispensed medications, and laboratory tests.
- Support for discounts, hospital service tax, and insurance policy claims.
- Payment tender processing: Cash, POS Credit Card, Debit Card, Direct Bank Transfer, and Third-Party Insurance.
- Clean printable invoice receipt formatted for thermal printers and standard A4 paper (`window.print()`).

### 9. Admin Bulk Data Import Hub
- Specialized high-speed bulk ingestion engine supporting:
  - **Patients Master Data**
  - **Physicians & Specialists Roster**
  - **Pharmaceutical Formulary & Inventory**
  - **Laboratory Diagnostic Test Catalog**
  - **Clinical Departments**
- **1-Click Downloadable Sample CSV Templates** directly from the user interface.
- High-integrity transaction processing: Invalid rows are flagged with row numbers and exact validation failure reasons without halting valid records.
- Permanent historical import audit log tracking file name, records read, success count, failure count, and errors.

### 10. Security & Immutable Audit Trail
- System-wide audit log tracking `CREATE`, `UPDATE`, `DELETE`, `BULK_IMPORT`, `DISPENSE`, `ADMISSION`, and `LOGIN` events.
- Captures authenticated user ID, target module, record ID, textual action description, client IP address, and browser user agent.

---

## 3. Technology Stack & Specifications

| Layer | Technology | Specification / Version |
|---|---|---|
| **Backend Framework** | Laravel | **13.36.0** (PHP 8.3 / 8.4) |
| **Primary Database** | PostgreSQL | **PostgreSQL 18** (`pdo_pgsql`) |
| **Styling & Components** | Bootstrap | **5.3.8** (Locally bundled in `public/vendor/bootstrap/`) |
| **Iconography** | Bootstrap Icons | **1.13.2** (Locally bundled in `public/vendor/bootstrap-icons/`) |
| **Offline Font Assets** | WOFF2 & WOFF | Bundled in `public/vendor/bootstrap-icons/fonts/` |
| **Testing Suite** | PHPUnit | **12.5.39** with in-memory SQLite runner |
| **Code Style** | Laravel Pint | **1.32.1** (PSR-12 / Laravel Preset) |

---

## 4. Default Medical Staff Accounts & Roles

The system is pre-seeded with complete sample accounts for each hospital department. Use password: `password` for all demo accounts:

| Role | Name | Email | Default Password | Primary Permissions |
|---|---|---|---|---|
| **Super Admin** | System Administrator | `admin@hospital.local` | `password` | Universal access, Bulk Import, Settings, Audit Logs |
| **Doctor** | Dr. Robert Chen, MD | `dr.chen@hospital.local` | `password` | Consultations, EMR, Prescriptions, Lab Orders |
| **Doctor** | Dr. Sarah Jenkins, MD | `dr.jenkins@hospital.local` | `password` | Pediatric Consultations, EMR encounters |
| **Nurse** | Nurse Emily Watson, RN | `nurse@hospital.local` | `password` | Triage, Vital Signs recording, Inpatient care |
| **Pharmacist** | James Miller, PharmD | `pharmacist@hospital.local` | `password` | Formulary, Stock alerts, Prescription dispensing |
| **Lab Technologist** | David Kim, MLS | `lab@hospital.local` | `password` | Specimen tracking, Laboratory findings entry |
| **Receptionist** | Amanda Brooks | `reception@hospital.local` | `password` | Patient intake, Appointment scheduling |
| **Accountant** | Michael Sterling, CPA | `billing@hospital.local` | `password` | Invoices, Cashier payments, Financial reports |

---

## 5. Setup & Installation Guide

### Prerequisites
1. **PHP**: Version 8.3 or 8.4 with extensions: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `intl`.
2. **Composer**: Version 2.x.
3. **Database**: PostgreSQL 18 (or PostgreSQL 15/16/17; also supports SQLite for testing).
4. **Environment**: Laragon, XAMPP, Native Windows, Linux, or Docker.

### Step 1: Environment Configuration
Copy `.env.example` to `.env` (or verify existing `.env`):

```sh
cp .env.example .env
```

Ensure your `.env` contains your PostgreSQL 18 connection credentials:

```env
APP_NAME="Hospital Information System"
APP_ENV=local
APP_KEY=base64:xYoCIFOWBg9wQ+Dsz+jzEhouqr9j9xOvcXl6v6bNe0k=
APP_DEBUG=true
APP_URL=http://localhost:8000

# PostgreSQL 18 Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hospital_his
DB_USERNAME=postgres
DB_PASSWORD=postgres
DB_SSLMODE=prefer
```

> **Note on Local Development without PostgreSQL 18 Installed:**
> If PostgreSQL 18 is not yet installed on your development workstation, you can immediately run the Hospital Information System using the pre-configured local SQLite database by toggling:
> ```env
> DB_CONNECTION=sqlite
> ```
> Both schemas are 100% cross-compatible.

### Step 2: Database Initialization in PostgreSQL 18
Open PostgreSQL CLI (`psql`) or pgAdmin and create the database:

```sql
CREATE DATABASE hospital_his;
```

### Step 3: Run Migrations & Hospital Data Seeder
Execute the database migrations and seed the enterprise clinical data:

```sh
php artisan migrate
php artisan db:seed
```

### Step 4: Start the Web Application
Launch the local web server:

```sh
php artisan serve --host=0.0.0.0 --port=8000
```

Open your browser and navigate to:
```
http://localhost:8000/login
```

Log in using `admin@hospital.local` and password `password`.

---

## 6. Hospital Intranet & Air-Gapped Network Configuration

To deploy the Hospital Information System on a hospital local area network (LAN) so that all doctor consultation rooms, nurses' stations, pharmacy counters, and laboratory terminals can access the system without internet:

1. **Host Binding**:
   When launching via Artisan or Laragon, bind to `0.0.0.0`:
   ```sh
   php artisan serve --host=0.0.0.0 --port=8000
   ```
2. **Network IP Resolution**:
   Determine the host server's local static IP address (e.g. `192.168.1.150`).
3. **Client Terminal Access**:
   Hospital workstation terminals navigate to:
   ```
   http://192.168.1.150:8000
   ```
4. **Zero CDN Verification**:
   All CSS, Javascript, SVG, and font files are loaded from `public/vendor/`. No external CDNs are contacted, guaranteeing zero latency and compliance with patient data isolation policies.

---

## 7. Admin Bulk Data Import Specification

To access the bulk import module:
1. Sign in as an administrator (`admin@hospital.local`).
2. Navigate to **Administration > Bulk Data Import** (`/admin/bulk-import`).
3. Select the desired entity dataset:
   - **Patients**: Medical records, contact info, blood group, allergies, chronic conditions.
   - **Doctors**: Specialists, medical license numbers, department codes, consultation fees.
   - **Medicines**: Drug formulary, brand/generic names, stock levels, batch numbers, unit prices.
   - **Lab Test Types**: Pathology & radiology test catalog, price tariffs, turnaround times.
   - **Departments**: Clinical and administrative departments.
4. Click **Download Sample CSV Template** to obtain the exact header format.
5. Populate the file and upload via the file ingestion field.
6. Review the resulting **Import Execution Report** for row-by-row confirmation.

### Sample CSV Structure for Patients:
```csv
mrn,first_name,last_name,email,phone,date_of_birth,gender,blood_group,genotype,national_id,address,emergency_contact_name,emergency_contact_phone,emergency_contact_relation,allergies,chronic_diseases,insurance_provider,insurance_policy_number
MRN-2026-9001,Alexander,Wright,alex@example.com,+1-555-0143,1985-04-12,male,O+,AA,NID-884129,742 Evergreen Terrace,Mary Wright,+1-555-0199,Spouse,Penicillin; Aspirin,Hypertension,BlueCross,BC-889211
```

---

## 8. Verification & Quality Assurance

The Hospital Information System includes a comprehensive automated test suite verifying authentication, patient record management, clinical workflows, prescription dispensing, and CSV bulk import validation.

Run the test suite:
```sh
php artisan test
```

Run Laravel Pint code formatter:
```sh
vendor/bin/pint
```

---

## License

The Clinic Management System is open-sourced software licensed under the [MIT license](LICENSE).
