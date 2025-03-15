# symfony_test
=======
# Backend Developer Assignment – User Data Management & Twitter OAuth API

## Overview of project
This project is a Symfony-based backend that manages user data from a CSV file, only admin role upload csv files and saves that data o database and other part is integrates Twitter OAuth authentication. It includes API endpoints for user management, database backup/restore, and Twitter authentication.

## Technology Stack
- **Language:** PHP
- **Framework:** Symfony
- **Database:** MySQL
- **Authentication:** Twitter OAuth 1.0a
- **Email Service:** Symfony Mailer with Mailgun

---

## **API Endpoints**

### **Part 1: User Data Management API**

#### 1. Upload and Store Data API
- **Endpoint:** `POST /api/upload`
- **Description:** Allows an admin to upload a CSV file containing user details.
- **Functionality:**
  - Parses the uploaded `data.csv` file.
  - check file empty or not or correct file type.
  - only admin role can upload file.
  - Saves user data into the MySQL database.
  - Sends an email notification to each user asynchronously.

#### 2. View Data API
- **Endpoint:** `GET /api/users`
- **Description:** Retrieves all stored user data.

#### 3. Backup Database API
- **Endpoint:** `GET /api/backup`
- **Description:** Allows an admin to generate a backup of the database as `backup.sql`.

#### 4. Restore Database API
- **Endpoint:** `POST /api/restore`
- **Description:** Restores the database using a provided to /var/backups/backup.sql path name `backup.sql` file.

---

### **Part 2: Twitter OAuth Integration**

#### 1. Initiate Twitter Authentication
- **Endpoint:** `GET /auth/twitter`
- **Description:** Redirects the user to Twitter for authentication.

#### 2. Handle Twitter Callback
- **Endpoint:** `GET /auth/twitter/callback`
- **Description:** Handles the OAuth response, fetches user details, stores them in MySQL, and redirects the user back to the app.

---

## ** part 3 : Installation & Setup**

### **1. Clone the Repository**
```sh
git clone <repository-url>
cd backend
```
### **2. Install the dependencies**
```sh
composer install
```
### **3. Set Up enviorment variables**

```sh
DATABASE_URL="mysql://username:password@127.0.0.1:3306/db_name"
MAILER_DSN=smtp://Yourname@Yourdomain.com:Yourpassword@smtp.mailgun.org:587
TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret

```
Replace username with you database username as well as the password and enter the name of the database.
First create developer account on twitter . From thai site you will get twitter client Id and Secret Id repplace this in this env file. 
```

### **4. Run database migration**

```sh
php bin/console doctrine:migrations:migrate

```

### **5. Start symfony server**

```sh
symfony server:start
```
###  or you can start PHP server**

```sh
php -S 127.0.0.1:8000 -t public
```

## Email Sending with Mailgun
- **This project uses Mailgun via Symfony Mailer for sending email notifications.
- **Ensure you have your domain verified and have a valid email credentials
- **Update the MAILER_DSN in your .env file with your Mailgun credentials


## **Example API Responses**  

1. **POST /api/upload** – Upload CSV and store user data  
   **Success Response:**  
   ```json  
  
    "message": "Data processed successfully",
    "inserted_records": [
        {
            "name": "test",
            "username": "test",
            "email": "test@gmail.com",
            "address": "nashik",
            "role": "user"
        },
        {
            "name": "test_1",
            "username": "test_1",
            "email": "test_1@gmail.com",
            "address": "nsk",
            "role": "admin"
        },
        {
            "name": "user1",
            "username": "user1",
            "email": "user1@gmail.com",
            "address": "nsk",
            "role": "user"
        }
    ],
    "skipped_record": 1

   {
      "message": "No new users inserted. Duplicate records found.",
      "inserted_records": 0,
      "skipped_record": 4
   }

  – skipped record shows found 1 duplicate record. Not inserted
  – inserted record shows inserted record count.  

   ```  
   **Error Response:**  
   ```json  
   {  
     "error": "Invalid file format. Please upload a valid CSV file."  
   }  
   - If file not selected to upload.
   {
    "error": "Only ADMIN users can upload files"
   }
   – if non admin user select file, it will show error.  
   {
    "error": "Email is required"
   }
   - If email not given.

   ```  

2. **GET /api/users** – View all stored user data  
   **Success Response:**  
   ```json  
   [  
      {
        "id": 1,
        "name": "admin",
        "email": "admin@gmail.com",
        "username": "test_admin",
        "address": "usa",
        "roles": "admin"
    },
    {
        "id": 52,
        "name": "test",
        "email": "test@gmail.com",
        "username": "test",
        "address": "nashik",
        "roles": "user"
    },
    {
        "id": 53,
        "name": "test_1",
        "email": "test_1@gmail.com",
        "username": "test_1",
        "address": "nsk",
        "roles": "admin"
    },
    {
        "id": 54,
        "name": "user1",
        "email": "user1@gmail.com",
        "username": "user1",
        "address": "nsk",
        "roles": "user"
    }
   ]  
   
   ```  

3. **GET /auth/twitter** 
   – Initiate Twitter Authentication  
   - This will redirect the user to Twitter’s login page.  

4. **GET /auth/twitter/callback** – Handle Twitter OAuth callback  
   **Success Response:**  
   ```json  
   {  
     "message": "User authenticated successfully.",  
     "user": {  
       "name": "admin",  
       "twitter_id": "1234567890"  
     }  
   }  
   ``` 
