# Pineapple-Chat
project_name: PineappleChat
description: >
  PineappleChat is a lightweight web-based chat system built with PHP and MySQL.
  It supports user registration, login, friend management, real-time messaging, and profile editing.
  The UI is responsive and mobile-friendly, making it ideal for learning, testing, and showcasing.

requirements:
  - XAMPP (includes Apache, PHP, and MySQL)
  - Web browser (Chrome recommended)
  - PHP version: 7.4 or above
  - MySQL database

installation_steps:
  - step: Download and install XAMPP
    details: >
      Visit https://www.apachefriends.org, download the installer, then start Apache and MySQL via the XAMPP Control Panel.

  - step: Move the project into htdocs
    details: >
      Copy the entire pineapplechat folder into XAMPP's htdocs directory.
      Example path: C:\xampp\htdocs\pineapplechat\

  - step: Import the database
    details: >
      Go to http://localhost/phpmyadmin, create a new database named pineapplechat,
      then import the included pineapplechat.sql file.

  - step: Launch the project
    details: >
      Visit http://localhost/pineapplechat/ in your browser to access the login page and start using the app.

configuration:
  database:
    host: localhost
    user: root
    password: ""
    name: pineapplechat

features:
  - User registration and login (with avatar, nickname, and bio)
  - Friend requests (send, accept, reject)
  - Real-time chat (with timestamp, IP address, device info)
  - Profile editing and avatar upload
  - Account deletion
  - Responsive UI for mobile and tablet

folder_structure:
  index.php: Login page
  register.php: Registration page
  home.php: User dashboard (friend list and search)
  chat.php: Chat interface
  fetch_messages.php: AJAX polling for new messages
  handle_request.php: Handles friend request logic
  profile.php: Profile editing page
  uploads/: Directory for storing uploaded avatars
  pineapplechat.sql: SQL file containing database schema and sample data

notes: >
  This project is intended for educational use. Before deploying publicly,
  it is recommended to implement SQL injection protection (using prepared statements),
  limit file upload size and types, and add CAPTCHA for enhanced security.
