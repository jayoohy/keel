Product Requirements Document (PRD)
Project Name
Keel
Alternative Names:
WealthMap
FundFlow
FutureFund
GoalBank
Version: 1.0
Status: Planning
Owner: Stilt-Tech Digital Solutions
Executive Summary
Keel is a goal-driven personal finance management platform designed to help users understand,
organize, and grow their finances through automated bank integrations, intelligent transaction tracking,
savings goal management, financial forecasting, and AI-powered insights.
Unlike traditional expense trackers that focus solely on spending, Keel focuses on helping users achieve
life goals such as:
Buying a house
Getting married
Relocating abroad
Building an emergency fund
Starting a business
Purchasing a vehicle
Funding education
The platform connects directly to users' bank accounts using Mono, automatically imports transactions,
categorizes spending patterns, allocates funds toward goals, and forecasts financial outcomes.
•
•
•
•
•
•
•
•
•
•
•
1
Problem Statement
Most individuals struggle to answer critical financial questions:
Where is my money going?
How much am I actually saving?
Am I on track to buy a house?
Can I afford relocation within the next 2 years?
How much do I need to save monthly to reach my goals?
Existing budgeting applications focus heavily on expense tracking but provide limited support for long-term
financial planning and goal achievement.
Keel addresses this gap by transforming financial data into actionable goal-driven insights.
Product Vision
To become the operating system for personal financial planning by helping users connect their finances
directly to their life goals.
Product Objectives
Primary Objectives:
Connect and synchronize financial data automatically from banks.
Provide a centralized view of financial health.
Track and forecast progress toward financial goals.
Reduce manual financial management.
Deliver intelligent financial recommendations.
Target Users
Primary Users
Professionals
Examples:
Software Developers
Remote Workers
•
•
•
•
•

1.
2.
3.
4.
5. •
   •
   2
   Freelancers
   Entrepreneurs
   Corporate Employees
   Needs:
   Track income
   Manage expenses
   Save for future goals
   Monitor cash flow
   Secondary Users
   Families and Couples
   Needs:
   Shared savings goals
   Wedding planning
   Home ownership planning
   Family budgeting
   User Stories
   Bank Connectivity
   As a user,
   I want to connect my bank account
   So that my transactions are automatically imported.
   Goal Creation
   As a user,
   I want to create savings goals
   So that I can monitor progress toward important life objectives.
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   3
   Financial Forecasting
   As a user,
   I want the system to forecast future balances
   So that I can plan confidently.
   Automated Allocation
   As a user,
   I want salary deposits to automatically allocate funds to my goals
   So that I can save consistently.
   Core Features
6. Authentication & User Management
   Features
   Registration
   Login
   Password Reset
   Email Verification
   Two-Factor Authentication
   Requirements
   Secure authentication
   Session management
   User preferences
7. Bank Integration
   Provider:
   Mono
   •
   •
   •
   •
   •
   •
   •
   •
   4
   Features
   Connect bank accounts
   Account authorization
   Account synchronization
   Reconnection handling
   Data Retrieved
   Account Information
   Available Balance
   Transaction History
   Income Data
   Institution Details
   Requirements
   Secure token storage
   Automatic synchronization
   Sync history logging
8. Account Management
   Features
   View:
   Connected accounts
   Current balances
   Institution information
   Metrics
   Total Cash Position
   Total Connected Accounts
   Combined Balances
9. Transaction Management
   Features
   Automatic transaction import
   Search transactions
   Filter transactions
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   •
   5
   Transaction details
   Categorization
   Transaction Types
   Debit
   Credit
   Transfer
   Fee
   Salary
   Refund
   Categories
   Default Categories:
   Food
   Transport
   Utilities
   Housing
   Shopping
   Entertainment
   Healthcare
   Education
   Investment
   Savings
   Custom categories supported.
10. Savings Goals
    Features
    Create goals such as:
    House Fund
    Wedding Fund
    Relocation Fund
    Emergency Fund
    Car Fund
    Business Fund
    Vacation Fund
    Goal Properties
    Name
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    6
    Description
    Target Amount
    Current Amount
    Deadline
    Priority
    Status
    Status Types
    Active
    Completed
    Paused
    Cancelled
11. Goal Allocation Engine
    Purpose
    Virtually allocate money toward goals without moving actual funds.
    Example
    Total Balance:
    ₦1,000,000
    Allocated:
    House Fund → ₦400,000
    Relocation Fund → ₦300,000
    Emergency Fund → ₦200,000
    Available:
    ₦100,000
    Allocation Types
    Automatic
    Manual
    Recurring
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    7
    Percentage Based
    Fixed Amount
12. Rules Engine
    Features
    Create automation rules.
    Examples:
    When salary arrives:
    20% → House Fund
    10% → Emergency Fund
    15% → Relocation Fund
    When freelance payment arrives:
    30% → Investment Fund
13. Financial Dashboard
    Overview Metrics
    Total Balance
    Monthly Income
    Monthly Expenses
    Monthly Savings
    Savings Rate
    Net Cash Flow
    Visual Components
    Spending Breakdown
    Goal Progress
    Income Trends
    Expense Trends
    Monthly Summary
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    8
14. Financial Forecasting
    Features
    Predict:
    Future balances
    Savings growth
    Goal completion dates
    Cash flow trends
    Example
    Current Savings Rate:
    ₦150,000/month
    House Goal:
    ₦25,000,000
    Forecast Completion:
    May 2032
15. Financial Health Score
    Score Range
    0–100
    Factors
    Savings Rate
    Emergency Fund Coverage
    Goal Progress
    Spending Stability
    Income Consistency
    •
    •
    •
    •
    •
    •
    •
    •
    •
    9
16. AI Insights
    Transaction Categorization
    Automatically categorize transactions.
    Example:
    "KFC Port Harcourt"
    → Food
    Spending Insights
    Example:
    You spent 27% more on transportation this month than last month.
    Goal Insights
    Example:
    At your current savings rate, your relocation goal will be achieved in 18 months.
    Anomaly Detection
    Example:
    Large transaction detected.
    Amount exceeds your average spending by 300%.
17. Notifications
    Notification Types
    Goal Updates
    Budget Alerts
    10
    Large Spending Alerts
    Goal Completion Alerts
    Synchronization Errors
    Rule Execution Alerts
    Non-Functional Requirements
    Performance
    Dashboard Load Time:
    < 3 Seconds
    Transaction Search:
    < 1 Second
    Synchronization:
    < 30 Seconds
    Security
    HTTPS Only
    Encrypted Tokens
    Secure Credential Storage
    Audit Logs
    Two-Factor Authentication
    CSRF Protection
    Reliability
    System Availability:
    99.9%
    Automatic Retry Mechanisms
    •
    •
    •
    •
    •
    •
    11
    Error Monitoring
    Data Model Overview
    Users
    ↓
    Bank Connections
    ↓
    Accounts
    ↓
    Transactions
    ↓
    Categories
    ↓
    Goals
    ↓
    Allocations
    ↓
    Rules
    ↓
    Forecasts
    ↓
    Notifications
    12
    Technology Stack
    Backend
    Laravel 12+
    PHP 8.4+
    PostgreSQL
    Redis
    Laravel Horizon
    Laravel Reverb
    Frontend
    React
    TypeScript
    Inertia.js
    Tailwind CSS
    TanStack Table
    Recharts
    AI
    OpenAI API
    Banking
    Mono
    Infrastructure
    Railway / VPS
    Cloudflare
    S3-Compatible Storage
    MVP Scope
    Phase 1
    Authentication
    Mono Integration
    Account Synchronization
    Transaction Synchronization
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    13
    Dashboard
    Transaction History
    Phase 2
    Savings Goals
    Goal Allocation Engine
    Rules Engine
    Goal Progress Tracking
    Notifications
    Phase 3
    Forecasting Engine
    Financial Health Score
    AI Categorization
    AI Insights
    Advanced Analytics
    Success Metrics
    User Success
    Bank connected successfully
    First goal created
    First automated allocation configured
    Product Success
    95% sync success rate
    80% transaction categorization accuracy
    < 3 second dashboard load time
    •
    •
    •
    •
    •
    •
    14
    Financial Success
    Users consistently contribute toward goals
    Increased monthly savings rates
    Reduced unnecessary spending
    Future Roadmap
    Version 2
    Shared Family Accounts
    Couple Savings Goals
    Group Savings
    Version 3
    Investment Tracking
    Stock Portfolio Tracking
    Treasury Bill Tracking
    Version 4
    AI Financial Coach
    Financial Planning Assistant
    Wealth Growth Recommendations
    Version 5
    Cooperative Savings
    Business Finance Management
    Financial Marketplace
    Product Mission
    Help users transform financial data into meaningful life outcomes by connecting everyday spending and
    saving decisions directly to long-term goals.
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    •
    15
