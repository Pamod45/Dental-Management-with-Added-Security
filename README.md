# Dental-Management-with-Added-Security

## Contributors 🏅
* [Vishan Perera](https://github.com/VishanPerera)
* [Nisal Wickramaarachchi](https://github.com/NisalWick2002)

## Project Overview 📋
The **Dental Surgery Data Management System** is an extensive project utilizing HTML, JavaScript, Bootstrap, CSS, PHP, MySQL, Figma, Visual Studio Code, and WAMP server. This system streamlines the management of appointments, patient records, and other critical dental surgery operations, enhancing productivity and efficiency.Authentication, authorization, Integrity are all maintained with enhance security features.

## Table of Contents 📚
- [Overview](https://github.com/NisalWick2002/temp?tab=readme-ov-file#overview-)
- [Objectives](https://github.com/NisalWick2002/temp?tab=readme-ov-file#objectives-)
- [Operations](https://github.com/NisalWick2002/temp?tab=readme-ov-file#operations-)
- [Features](https://github.com/NisalWick2002/temp?tab=readme-ov-file#features-)
- [Technologies Used](https://github.com/NisalWick2002/temp?tab=readme-ov-file#technologies-#used-)
- [Installation](https://github.com/NisalWick2002/temp?tab=readme-ov-file#installation-)
- [Usage](https://github.com/NisalWick2002/temp?tab=readme-ov-file#usage-)
- [Security Measures](https://github.com/NisalWick2002/temp?tab=readme-ov-file#security-measures-)
- [Contributing](https://github.com/NisalWick2002/temp?tab=readme-ov-file#contributing)
- [License](https://github.com/NisalWick2002/temp?tab=readme-ov-file#license)

## Overview 📋
This comprehensive project represents the culmination of year-end efforts. Designed as a robust solution, it efficiently manages vast amounts of data with precision, elevating the dental surgery it serves to a pinnacle of excellence. Key features include appointment scheduling, patient records management, and real-time updates for medical practitioners.

## Objectives 🎯
- **Streamline Data Management**: Replace time-consuming paper-based systems with efficient digital processes.
- **Enhance Security**: Implement data encryption, password protection, and input validation to safeguard sensitive data.
- **Ensure Data Integrity**: Regular backups and data recovery procedures ensure uninterrupted operations.
- **Real-Time Updates**: Medical practitioners can easily access and update schedules in real-time.
- **Automated Reporting**: Management benefits from automated facility request processing and streamlined reporting.
- **Accurate Calculations**: Transition from manual to computerized methods for improved precision.
- **Patient Access**: Patients can securely view and download their medical records.

### Pre-Implementation Process
Before implementing this solution, dental center operations relied heavily on manual processes for appointment scheduling, patient data recording, and treatment management. These methods were time-consuming and prone to errors, often requiring significant staff intervention.

## Operations 🔧
- **Appointment Scheduling**: Previously handled manually by staff, doctor availability and patient appointments are now seamlessly managed through the system.
- **Patient Records**: Medical records, once maintained manually by patients, are now securely stored and accessed digitally.
- **Employee Management**: Streamlined employee management and pharmaceutical inventory control ensure precise record-keeping and operational efficiency.
  
The project revolutionizes dental surgery operations with a technology-driven approach to data management and healthcare provision.

## Features 🔐
- **Captcha Integration**: Protects against bots.
- **Password Security**: Bcrypt hashing with salting ensures secure password storage.
- **User Validation**: Stringent username and password validation (8-12 characters with capital letters, numbers, and special characters).
- **Input Sanitization**: Prevents malicious input, ensuring system integrity.
- **Parametrized SQL Queries**: Guards against SQL injection attacks.
- **Session Management**: Secure sessions with HTTPS, SameSite cookie attributes, and session timeout.
- **MongoDB for Logging**: Tracks user login attempts securely.
- **Database User Permissions**: Restricted access to the database based on user roles.

## Technologies Used
- **Frontend**: HTML, JavaScript, Bootstrap, CSS, Figma
- **Backend**: PHP, MySQL, MongoDB
- **Tools**: Visual Studio Code, WAMP server

## Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/NisalWick2002/Dental-Surgery-Data-Management-System.git
   cd dental-surgery-management
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Set up environment variables:
   Create a `.env` file with the following:
   ```env
   DB_HOST=<your-database-host>
   DB_USER=<your-database-user>
   DB_PASS=<your-database-password>
   SESSION_SECRET=<your-session-secret>
   ```

4. Start the application:
   ```bash
   npm start
   ```

## Usage
1. Navigate to `http://localhost:3000`.
2. Sign up or log in to start managing dental surgery data, including appointments and patient records.

## Security Measures
- **Password Hashing with Bcrypt**: Secure password storage with hashing and salting.
- **Captcha**: Prevents automated login attempts.
- **User Input Validation**: Ensures strong, secure user authentication.
- **Database Security**: Parametrized SQL queries, transactions, and database user permissions.

## Contributing
Contributions are welcome! Please open an issue or submit a pull request.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
