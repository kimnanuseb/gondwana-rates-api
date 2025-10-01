# Gondwana Rates API Assignment

This repository contains the coding assignment for the **Software Developer role at Gondwana**.  
It implements both a **RESTful PHP API** and a **simple frontend UI** to fetch and display live rates from Gondwana’s system.

---

## 🎯 Objective (per assignment brief)
- ✅ Develop a **RESTful API** using PHP  
- ✅ Construct a **simple UI** to interact with the API  
- ✅ Ensure compatibility with **GitHub Codespaces**  
- ✅ Integrate **SonarCloud QA checks** on pull requests  

---

## 🛠 Tech Stack
- **Backend**: PHP 8.3, cURL for API calls
- **Frontend**: HTML + vanilla JavaScript (minimal CSS)
- **Testing**: PHPUnit with coverage reporting
- **QA**: SonarCloud GitHub Action

---

## 📂 Project Structure
/frontend
index.html # User interface
/api
index.php # REST API wrapper
/tests
ApiTest.php # PHPUnit tests

yaml
Copy code

---

## 🔗 API Details

### Input Payload
```json
{
  "Unit Name": "String",
  "Arrival": "dd/mm/yyyy",
  "Departure": "dd/mm/yyyy",
  "Occupants": 2,
  "Ages": [30, 12]
}
Mutated Payload (sent to remote Gondwana API)
json
Copy code
{
  "Unit Type ID": -2147483637,
  "Arrival": "yyyy-mm-dd",
  "Departure": "yyyy-mm-dd",
  "Guests": [
    { "Age Group": "Adult" },
    { "Age Group": "Child" }
  ]
}
Response (relayed to frontend)
Unit Name

Availability

Rate

Date Range

▶️ Running Locally in Codespaces
Start PHP server from repo root:

bash
Copy code
php -S localhost:8080 -t frontend
Then open in browser:

arduino
Copy code
http://localhost:8080
🧪 Running Tests
Install dependencies:

bash
Copy code
composer install
Run tests with coverage:

bash
Copy code
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text

✅ Assignment Deliverables Mapping
Backend API: implemented in frontend/api/index.php

Payload ingestion & mutation: handled before sending to remote Gondwana endpoint

Frontend UI: frontend/index.html

Data displayed: unit name, availability, date range, and rates

Testing: PHPUnit with coverage enabled

QA Automation: SonarCloud GitHub Action included

Runs in Codespaces: tested and verified

👤 Author
Full Name: Dr Kim Nanuseb

GitHub Handle: @kimnanuseb
