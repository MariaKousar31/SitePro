CarbonTrack Pro — User Workflow Overview
1. Project Creation (Foundation Layer)
Purpose

The project is the central entity of the system. All data such as emissions, materials, equipment usage, and targets are linked to a specific project.

User Actions
Create a new project
Enter basic project details such as:
Project name
Location (if applicable)
Optional metadata (status, dates, etc.)
System Outcome
A unique ProjectID is generated
This ID becomes the reference key for all other modules
2. Material Setup (Master Data Layer)
Purpose

Materials define the emission factors used in calculations. They act as the baseline for all CO₂ computations in the system.

User Actions
Add construction materials such as cement, steel, sand, etc.
Provide:
Material name
Category (optional)
Unit of measurement
Emission factor (CO₂ per unit)
System Outcome
Materials are stored as reusable reference data
Each material has an emission factor used in calculations later
3. Element Logging (Core Emission Tracking)
Purpose

This is the primary emission calculation module. It records material usage within a project.

User Actions
Select a project
Select a material
Enter volume or quantity used
Optionally assign a phase and add notes
System Processing
The system calculates emissions using:
CO₂ = Volume × Emission Factor
The calculated value is stored in the emissions table
Audit logs are created for traceability
System Outcome
Each element represents a real-world material usage event
This forms the main source of project emissions
4. Equipment Management and Assignment
Purpose

Tracks emissions caused by machinery and equipment used in projects.

User Actions
Add equipment details:
Equipment name
Type
Fuel type
CO₂ emission rate per hour
Daily cost (optional)
Assign equipment to a project:
Select project
Select equipment
Enter hours used
Define start and end dates
System Processing
Calculates emissions:
CO₂ = Hours Used × CO₂ per Hour
System Outcome
Equipment-based emissions are linked to projects
Operational carbon footprint is captured
5. Supplier Management and Material Linking
Purpose

Manages supplier relationships and connects suppliers to materials.

User Actions
Add supplier companies
Provide:
Company name
Contact details
Country
Certification status (optional)
Link suppliers to materials:
Select material
Select supplier
Enter unit price
Enter lead time
System Outcome
Supply chain relationships are established
Cost and sourcing data is connected to materials
6. Carbon Target Definition
Purpose

Defines emission reduction goals for projects and compares actual emissions against targets.

User Actions
Select project
Set:
Target CO₂ value
Baseline CO₂ value
Target year
Optional notes
System Processing
Calculates actual emissions from elements
Compares:
Actual emissions vs target emissions
Computes progress percentage
System Outcome
Displays project progress toward sustainability goals
Indicates whether the project is on track or exceeding limits
7. Certification Management
Purpose

Tracks sustainability certifications and compliance status for projects.

User Actions
Select project
Select certification type
Enter:
Award date
Expiry date
Score
Status (Applied, In Review, Awarded, Expired)
System Outcome
Maintains certification records per project
Supports compliance tracking and reporting
Overall System Flow
Create Project
Define Materials (with emission factors)
Log Elements (material usage and emissions)
Assign Equipment (machine-based emissions)
Link Suppliers (supply chain data)
Set Carbon Targets (performance benchmarks)
Manage Certifications (compliance tracking)
System Summary

The platform functions as a project-based carbon accounting system. It combines material usage, equipment operations, and supply chain data to calculate total emissions and compare them against defined sustainability targets while also tracking certification compliance.