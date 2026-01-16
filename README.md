# Company X — Call Tracking Platform

A PHP-based call tracking and analytics platform.

## About This Repository

This repository is the starting point for the **DevOps Engineer assessment**. It contains a working application built by a contractor. Your job is to build the infrastructure around it.

## Application Overview

**What it does:**

-   Displays real-time call statistics dashboard
-   Stores call records in MySQL
-   Uses Redis for caching
-   Exposes REST API for mobile app (`/api.php`)
-   Receives webhooks from telephony provider

**Tech stack:**

-   PHP 8.1 with PHP-FPM
-   MySQL 8.0
-   Redis 7
-   Nginx

## API Endpoints

| Endpoint                  | Method | Description              |
| ------------------------- | ------ | ------------------------ |
| `/`                       | GET    | Dashboard UI             |
| `/api.php?action=health`  | GET    | Health check             |
| `/api.php?action=stats`   | GET    | Today's statistics       |
| `/api.php?action=calls`   | GET    | Recent calls (paginated) |
| `/api.php?action=webhook` | POST   | Receive call events      |

## Environment Variables

| Variable     | Description       | Default     |
| ------------ | ----------------- | ----------- |
| `DB_HOST`    | MySQL hostname    | localhost   |
| `DB_NAME`    | Database name     | talkmetrics |
| `DB_USER`    | Database user     | root        |
| `DB_PASS`    | Database password | (empty)     |
| `REDIS_HOST` | Redis hostname    | localhost   |
| `REDIS_PORT` | Redis port        | 6379        |

## Current State

The application code works. Everything else is missing:

-   ❌ No containerization
-   ❌ No CI/CD pipeline
-   ❌ No infrastructure design
-   ❌ No server automation
-   ❌ No documentation

## Your Mission

See the assessment document for full details. You'll create:

-   ✅ Local development environment (Docker)
-   ✅ CI/CD pipeline (GitHub Actions or GitLab CI)
-   ✅ Production architecture design
-   ✅ Infrastructure as code (Terraform or equivalent)
-   ✅ Server provisioning script

## Repository Structure (After Your Work)

```
├── app/                           # Provided
│   ├── index.php
│   ├── api.php
│   ├── config.php
│   └── public/
│       └── styles.css
├── database/
│   └── init.sql                   # Provided - schema + sample data
├── docker/
│   ├── Dockerfile                 # You create
│   └── docker-compose.yml         # You create
├── infrastructure/
│   ├── terraform/                 # You create (Option A)
│   └── scripts/
│       └── setup-server.sh        # You create
├── .github/workflows/
│   └── ci.yml                     # You create
├── docs/
│   └── ARCHITECTURE.md            # You create
├── .env.example                   # Provided
└── README.md                      # Update this
```

## Getting Started

> After completing the assessment, replace this section with your setup instructions.

```bash
# Your commands here
```

---

_See the assessment document for requirements and evaluation criteria._
