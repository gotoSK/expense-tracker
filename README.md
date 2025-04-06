# expense-tracker

Setup instructions:
- Download & install XAMPP (with MySQL checked during installation process) in default location ```C:\xampp```
- Open a terminal:
  ```cd "C:\xampp\htdocs"``` and clone this repo ```git clone https://github.com/gotoSK/expense-tracker.git```
- From inside the cloned folder, move ```expense_tracker``` to ```C:\xampp\mysql\data```
- Open XAMPP Control Panel and start Apache & MySQL
- Open a browser and search ```http://localhost/expense-tracker/index.php```

About:
User Story: Expense Tracking System
Track and Categorize Expenses
As a User who wants to manage personal or business finances efficiently, I want to add my expenses and categorize them based on different spending types, so that I can easily track my spending habits, analyze my budget, and make informed financial decisions.
Acceptance Criteria
1. Expense Addition
○ Users can input an expense amount
○ Users can provide a description for the expense
○ Users can select a date for the expense
○ Users can assign a category (e.g., Food, Travel, Rent, Entertainment)
2. Expense Categorization
○ The system should provide predefined categories
○ Users can create custom categories
○ Users can filter and view expenses based on categories
3. Expense Overview
○ Users can view a list of their expenses
○ Users can see total spending per category
○ Users can export expenses as a report (CSV/PDF) with Letter Head.
4. User Authentication
○ Users can sign up and log in to save their data
○ Users can reset their password if needed
5. Budgeting
○ Users can set spending limits for categories
○ Users get alerts when nearing or exceeding the budget
