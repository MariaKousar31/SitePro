-- ============================================================
-- CarbonTrack Pro — Extended Database Schema
-- Course: SE-204 Database Management System
-- ============================================================

CREATE DATABASE IF NOT EXISTS co2_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE co2_db;

-- ─── 1. CLIENTS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Clients (
    ClientID      INT AUTO_INCREMENT PRIMARY KEY,
    CompanyName   VARCHAR(120) NOT NULL,
    ContactPerson VARCHAR(80),
    Email         VARCHAR(100),
    Phone         VARCHAR(30),
    Address       TEXT,
    Industry      VARCHAR(60),
    CreatedAt     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── 2. CONTRACTORS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Contractors (
    ContractorID  INT AUTO_INCREMENT PRIMARY KEY,
    Name          VARCHAR(120) NOT NULL,
    ContactEmail  VARCHAR(100),
    Phone         VARCHAR(30),
    LicenseNo     VARCHAR(60),
    Specialty     VARCHAR(80),
    Rating        DECIMAL(3,2) DEFAULT 0.00,   -- 0.00 – 5.00
    CreatedAt     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── 3. PROJECTS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Projects (
    ProjectID     INT AUTO_INCREMENT PRIMARY KEY,
    ClientID      INT,
    ContractorID  INT,
    Name          VARCHAR(120) NOT NULL,
    Location      VARCHAR(120),
    StartDate     DATE,
    EndDate       DATE,
    Budget        DECIMAL(15,2),               -- currency
    Status        ENUM('Planning','Active','On Hold','Completed','Cancelled') DEFAULT 'Planning',
    Description   TEXT,
    CreatedAt     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ClientID)     REFERENCES Clients(ClientID)     ON DELETE SET NULL,
    FOREIGN KEY (ContractorID) REFERENCES Contractors(ContractorID) ON DELETE SET NULL
);

-- ─── 4. PROJECT PHASES ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS Phases (
    PhaseID       INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID     INT NOT NULL,
    PhaseName     VARCHAR(80) NOT NULL,
    StartDate     DATE,
    EndDate       DATE,
    PhaseOrder    INT DEFAULT 1,
    Status        ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE
);

-- ─── 5. TEAMS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Teams (
    TeamID        INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID     INT NOT NULL,
    TeamName      VARCHAR(80) NOT NULL,
    Role          VARCHAR(80),                 -- e.g. Structural, MEP, Civil
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE
);

-- ─── 6. TEAM MEMBERS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS TeamMembers (
    MemberID      INT AUTO_INCREMENT PRIMARY KEY,
    TeamID        INT NOT NULL,
    FullName      VARCHAR(100) NOT NULL,
    Designation   VARCHAR(80),
    Email         VARCHAR(100),
    Phone         VARCHAR(30),
    JoinDate      DATE,
    HourlyRate    DECIMAL(8,2),               -- currency
    FOREIGN KEY (TeamID) REFERENCES Teams(TeamID) ON DELETE CASCADE
);

-- ─── 7. MATERIAL CATEGORIES ──────────────────────────────────
CREATE TABLE IF NOT EXISTS MaterialCategories (
    CategoryID    INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName  VARCHAR(80) NOT NULL,
    Description   TEXT
);

-- ─── 8. MATERIALS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Materials (
    MaterialID      INT AUTO_INCREMENT PRIMARY KEY,
    CategoryID      INT,
    Name            VARCHAR(120) NOT NULL,
    EmissionFactor  DECIMAL(10,4) NOT NULL,   -- kg CO₂ per unit
    Unit            VARCHAR(20) DEFAULT 'm3',
    Density         DECIMAL(10,3),            -- kg/m³ (numeric)
    RecycledContent DECIMAL(5,2) DEFAULT 0.00,-- percentage
    Description     TEXT,
    CreatedAt       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CategoryID) REFERENCES MaterialCategories(CategoryID) ON DELETE SET NULL
);

-- ─── 9. SUPPLIERS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Suppliers (
    SupplierID    INT AUTO_INCREMENT PRIMARY KEY,
    CompanyName   VARCHAR(120) NOT NULL,
    ContactPerson VARCHAR(80),
    Email         VARCHAR(100),
    Phone         VARCHAR(30),
    Country       VARCHAR(60),
    CertifiedGreen TINYINT(1) DEFAULT 0        -- boolean
);

-- ─── 10. MATERIAL SUPPLIERS (many-to-many) ───────────────────
CREATE TABLE IF NOT EXISTS MaterialSuppliers (
    MaterialID    INT NOT NULL,
    SupplierID    INT NOT NULL,
    UnitPrice     DECIMAL(10,2),              -- currency
    LeadTimeDays  INT,
    PRIMARY KEY (MaterialID, SupplierID),
    FOREIGN KEY (MaterialID)  REFERENCES Materials(MaterialID)  ON DELETE CASCADE,
    FOREIGN KEY (SupplierID)  REFERENCES Suppliers(SupplierID)  ON DELETE CASCADE
);

-- ─── 11. ELEMENTS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Elements (
    ElementID     INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID     INT NOT NULL,
    PhaseID       INT,
    MaterialID    INT NOT NULL,
    Volume        DECIMAL(12,4) NOT NULL,
    Notes         TEXT,
    LoggedAt      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ProjectID)  REFERENCES Projects(ProjectID)  ON DELETE CASCADE,
    FOREIGN KEY (PhaseID)    REFERENCES Phases(PhaseID)      ON DELETE SET NULL,
    FOREIGN KEY (MaterialID) REFERENCES Materials(MaterialID) ON DELETE RESTRICT
);

-- ─── 12. EMISSION CALCULATIONS ───────────────────────────────
CREATE TABLE IF NOT EXISTS EmissionCalculation (
    CalcID        INT AUTO_INCREMENT PRIMARY KEY,
    ElementID     INT NOT NULL UNIQUE,
    CO2_Emission  DECIMAL(14,4) NOT NULL,
    CalcMethod    VARCHAR(60) DEFAULT 'Volume×Factor',
    CalcDate      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ElementID) REFERENCES Elements(ElementID) ON DELETE CASCADE
);

-- ─── 13. EQUIPMENT ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Equipment (
    EquipmentID   INT AUTO_INCREMENT PRIMARY KEY,
    Name          VARCHAR(100) NOT NULL,
    Type          VARCHAR(60),               -- e.g. Crane, Excavator
    FuelType      VARCHAR(40),
    CO2PerHour    DECIMAL(8,3),              -- kg CO₂ / operating hour
    DailyRatePKR  DECIMAL(12,2)              -- currency (PKR)
);

-- ─── 14. PROJECT EQUIPMENT (many-to-many) ────────────────────
CREATE TABLE IF NOT EXISTS ProjectEquipment (
    ProjectID     INT NOT NULL,
    EquipmentID   INT NOT NULL,
    HoursUsed     DECIMAL(10,2) DEFAULT 0,
    StartDate     DATE,
    EndDate       DATE,
    PRIMARY KEY (ProjectID, EquipmentID),
    FOREIGN KEY (ProjectID)   REFERENCES Projects(ProjectID)   ON DELETE CASCADE,
    FOREIGN KEY (EquipmentID) REFERENCES Equipment(EquipmentID) ON DELETE CASCADE
);

-- ─── 15. CERTIFICATIONS ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS Certifications (
    CertID        INT AUTO_INCREMENT PRIMARY KEY,
    CertName      VARCHAR(100) NOT NULL,     -- e.g. LEED Gold, BREEAM
    IssuingBody   VARCHAR(100),
    ValidYears    INT DEFAULT 3
);

-- ─── 16. PROJECT CERTIFICATIONS (many-to-many) ───────────────
CREATE TABLE IF NOT EXISTS ProjectCertifications (
    ProjectID     INT NOT NULL,
    CertID        INT NOT NULL,
    AwardedDate   DATE,
    ExpiryDate    DATE,
    Score         DECIMAL(6,2),
    Status        ENUM('Applied','In Review','Awarded','Expired') DEFAULT 'Applied',
    PRIMARY KEY (ProjectID, CertID),
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE,
    FOREIGN KEY (CertID)    REFERENCES Certifications(CertID) ON DELETE CASCADE
);

-- ─── 17. CARBON TARGETS ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS CarbonTargets (
    TargetID      INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID     INT NOT NULL UNIQUE,
    TargetCO2_kg  DECIMAL(14,2) NOT NULL,
    BaselineCO2   DECIMAL(14,2),
    TargetYear    YEAR,
    Notes         TEXT,
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE
);

-- ─── 18. INSPECTIONS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Inspections (
    InspectionID  INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID     INT NOT NULL,
    InspectorName VARCHAR(100),
    InspDate      DATE,
    Type          ENUM('Structural','Environmental','Safety','Final') DEFAULT 'Environmental',
    Result        ENUM('Pass','Fail','Conditional') DEFAULT 'Pass',
    Notes         TEXT,
    FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID) ON DELETE CASCADE
);

-- ─── 19. AUDIT LOGS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS AuditLogs (
    LogID         INT AUTO_INCREMENT PRIMARY KEY,
    TableName     VARCHAR(60),
    RecordID      INT,
    Action        ENUM('INSERT','UPDATE','DELETE'),
    OldValue      TEXT,
    NewValue      TEXT,
    ChangedAt     DATETIME DEFAULT CURRENT_TIMESTAMP,
    ChangedBy     VARCHAR(80) DEFAULT 'system'
);

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT IGNORE INTO MaterialCategories (CategoryID, CategoryName, Description) VALUES
(1,'Concrete & Cement','Portland cement, ready-mix, precast'),
(2,'Steel & Metal','Rebar, structural steel, aluminum'),
(3,'Masonry','Bricks, blocks, stone'),
(4,'Timber & Wood','Structural timber, plywood, MDF'),
(5,'Glass & Glazing','Float glass, tempered, double-glazed'),
(6,'Insulation','Mineral wool, EPS, XPS'),
(7,'Finishes','Paint, plaster, tiles');

INSERT IGNORE INTO Certifications (CertID, CertName, IssuingBody, ValidYears) VALUES
(1,'LEED Gold','U.S. Green Building Council',5),
(2,'LEED Platinum','U.S. Green Building Council',5),
(3,'BREEAM Excellent','BRE Global',3),
(4,'ISO 14001','ISO',3),
(5,'EDGE Certified','IFC World Bank',5);

INSERT IGNORE INTO Equipment (EquipmentID, Name, Type, FuelType, CO2PerHour, DailyRatePKR) VALUES
(1,'Tower Crane 50T','Crane','Electric',2.4,45000.00),
(2,'Excavator CAT 320','Excavator','Diesel',18.6,35000.00),
(3,'Concrete Pump','Pump','Diesel',12.2,22000.00),
(4,'Bulldozer D6','Bulldozer','Diesel',21.0,30000.00),
(5,'Mobile Generator 100kVA','Generator','Diesel',25.5,8000.00);
