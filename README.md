# Inventory Management System
### Introduction
Welcome to our Inventory Management System documentation. Here, you'll find all the essential information to efficiently track, manage, and optimize your inventory. Dive in to learn how to streamline your processes and ensure seamless operations.
### Purpose of the API
In an **Inventory Management System (IMS)**, authentication and authorization ensure that users access only the appropriate areas of the system based on their role. Authentication verifies a user's identity, while authorization controls what actions they can perform.

## Prerequisites
1. **XAMPP** (PHP and MySQL environment)
2. A modern web browser (e.g., Chrome, Firefox, Edge)

## Installation Guide
1. ### Clone the repository
   1. Open your terminal or command prompt.
   2. Clone the repository using the following command:
      ```
      git clone https://github.com/Ivan-Lazz/imsfin
      ```
   3. Once cloned, navigate to the project directory:
      ```
      cd imsfin
      ```
2. ### Move Folders to XAMPP's htdocs
   1. Locate your XAMPP installation folder. The default path is typically:
      - Windows: ```C:\xampp\htdocs```
   2. Copy the following folder from the cloned repository into the htdocs directory:
      - imsfin
3. ### Set Up the Database
   1. Start **XAMPP** and ensure **Apache** and **MySQL** services are running. Then, open your browser and go to:
      - ``` http://localhost/phpmyadmin ```
   2. Create a new database:
      - Click on New in the left-side pane.
      - Name your database (e.g., php_ims) and click **Create**.
   3. Import the SQL file:
      - Inside the main folder, locate the SQL file (e.g., php_ims.sql).
      - Go to the database you created in phpMyAdmin.
      - Click Import and select the corresponding SQL file.

  
## Troubleshooting
   - If the website or Inventory Management doesn’t load, ensure that:
     - Apache and MySQL are running in XAMPP.
     - The folders are placed correctly in htdocs.
     - Database configurations (database.php) match your setup.
  
If you encounter a database error, check if you’ve imported the SQL files into the correct database.

## REST API Base URL
The base url for the api is: ``` http://localhost/imsfin/IMS_API/ ```

## API Endpoints and Uses
- ``` /api/auth/ ``` : verifies user credentials against the database and initiates a session for successful logins.
- ``` /api/dashboard/ ``` : provides statistical data related to various entities within the application, such as products, orders, and companies.
- ``` /api/user/ ``` : allowing administrators to maintain user roles and statuses effectively. 
- ``` /api/unit/ ``` : maintaining consistency in how products are measured and sold.
- ``` /api/company/ ``` : ensuring that all associated company data is accurate and up-to-date.
- ``` /api/party/ ``` : maintaining accurate records of all parties involved in business operations.
- ``` /api/product/ ``` : essential for inventory management and sales operations, allowing businesses to track and manage their product offerings effectively.
- ``` /api/sales/ ``` : ensures that all sales data is accurately recorded and can be accessed for reporting and analysis.
- ``` /api/stock/ ``` : preventing stockouts and overstock situations, helping businesses maintain optimal inventory levels.
- ``` /api/report/ ``` : providing insights into business performance and identifying trends over time.
       
