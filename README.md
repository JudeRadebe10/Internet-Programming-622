 1. Install XAMPP
Download XAMPP from apachefriends.org

Install it and launch the XAMPP Control Panel

Start the Apache and MySQL services

✅ 2. Clone the Project from GitHub
You can download or clone the repository using:

bash
Copy
Edit
git clone https://github.com/JudeRadebe10/Internet-Programming-622.git
📁 This will create a folder named Internet-Programming-622 containing the full project source code.

✅ 3. Move Project to XAMPP Directory
Move the cloned folder to:

makefile
Copy
Edit
C:\xampp\htdocs\
So the final path should be:
C:\xampp\htdocs\Internet-Programming-622\

✅ 4. Import the Database into phpMyAdmin
Open phpMyAdmin in your browser

Click "Databases" and create a new one named:

nginx
Copy
Edit
whatsapp_clone
Click the new database, then go to the "Import" tab

Choose the file:

pgsql
Copy
Edit
whatsapp_clone.sql
(located in the root of your cloned project folder)

Click "Go" to import the database

✅ 5. Update Database Configuration (if needed)
If your project has a config.php or db.php file, ensure the connection settings look like this:

php
Copy
Edit
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "whatsapp_clone";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
✅ 6. Run the Website
In your browser, go to:

arduino
Copy
Edit
http://localhost/Internet-Programming-622/
This will load the homepage of the WhatsApp Clone project.

📁 Project Structure Overview
bash
Copy
Edit
Internet-Programming-622/
│
├── whatsapp_clone.sql           # Database export file
├── index.php                    # Main entry file
├── style.css                    # Stylesheet
├── script.js                    # JavaScript functionality
├── /images                      # Image assets
├── /css, /js, etc.              # Optional other folders
└── README.md                    # This file
👤 Author
Jude Olisa Radebe: student number - 402308992

📧 Student at Richfield College – BSc in Information Technology

GitHub: @JudeRadebe10
