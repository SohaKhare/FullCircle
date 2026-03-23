# FullCircle — Food Donation Management System

A web-based platform connecting **food donors** with **NGOs** to reduce food waste and fight hunger.
Food wastage and food insecurity exist together, especially in urban areas. Large amounts of surplus food from restaurants, events, and households are thrown away daily, while many people still do not have access to proper meals. One major reason for this problem is the lack of an organized system to connect food donors with NGOs in real time.
---

## Quick Setup (XAMPP)

### Step 1 — Copy Project Files

Copy the `foodcircle` folder to:

```
C:\xampp\htdocs\foodcircle\
```

### Step 2 — Setup Database

1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
3. Click **"New"** → Create database named `food_donation_db`
4. Click the database → go to **"Import"** tab
5. Choose `foodcircle/database.sql` → Click **Go**

### Step 3 — Open the Website

Visit: `http://localhost/foodcircle/`

---

## Project Structure

```
foodbridge/
│
├── index.php              ← Homepage
├── login.php              ← Login
├── register.php           ← Register (Donor / NGO)
├── logout.php             ← Logout
├── contact.php            ← Contact Us
├── database.sql           ← Run this to setup DB
│
├── donor/
│   ├── donate_food.php    ← Add new donation
│   ├── my_donations.php   ← View/manage donations + approve NGO requests
│   └── edit_donation.php  ← Edit a donation
│
├── ngo/
│   ├── request_food.php   ← Browse & request available food
│   └── my_requests.php    ← Track request status
│
├── includes/
│   ├── db.php             ← Database connection
│   ├── auth.php           ← Session & role helpers
│   ├── header.php         ← Navbar (included on all pages)
│   └── footer.php         ← Footer (included on all pages)
│
├── css/
│   └── style.css          ← Full green theme + dark mode
│
└── js/
    └── script.js          ← Dark mode, modals, filters, animations
```

---

## How the Workflow Works

```
Donor registers → Adds food donation (status: available)
                         ↓
NGO registers → Browses donations → Clicks "Request"
                         ↓
         Request created (status: pending)
                         ↓
Donor sees request in My Donations → Approve / Reject
                         ↓
         If approved → status: approved
         NGO sees donor phone number for pickup coordination
                         ↓
Donor marks as Complete → donation status: completed
```

---

## Features

- Donor: Add, Edit, Delete donations
- NGO: Browse, Filter, Request donations
- Donor: Approve / Reject / Complete requests via modal popup
- NGO: Track request status, cancel pending requests
- Search & filter by food type and keyword
- Contact form saved to database
- Animated stats counter on homepage
- Live donation feed on homepage
- Role-based navigation and access control
- Responsive design (mobile friendly)

---

## 🛠️ Tech Stack

| Layer    | Technology                        |
| -------- | --------------------------------- |
| Frontend | HTML, CSS, JavaScript             |
| Backend  | PHP (mysqli)                      |
| Database | MySQL (via XAMPP)                 |
| Fonts    | Google Fonts (Fraunces + DM Sans) |

---

## 📋 Database Tables

| Table              | Purpose                    |
| ------------------ | -------------------------- |
| `users`            | Donors and NGOs            |
| `donations`        | Food listings by donors    |
| `requests`         | NGO requests for donations |
| `contact_messages` | Messages from contact form |

---
Developed by Soha Khare, Shreeya Madulkar and Sweekriti Maheshwari
