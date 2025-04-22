# Google Calendar Integration Setup Guide

## Overview
Own My Calendar requires Google Calendar integration to access and grade your calendar data. This guide will walk you through the process of setting up the necessary Google API credentials.

## Prerequisites
- A Google account
- Access to the [Google Cloud Console](https://console.cloud.google.com/)

## Step 1: Create a Google Cloud Project
1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top of the page
3. Click "New Project"
4. Enter a name for your project (e.g., "Own My Calendar")
5. Click "Create"

## Step 2: Enable the Google Calendar API
1. Select your newly created project
2. Go to "APIs & Services" > "Library"
3. Search for "Google Calendar API"
4. Click on "Google Calendar API" in the results
5. Click "Enable"

## Step 3: Configure OAuth Consent Screen
1. Go to "APIs & Services" > "OAuth consent screen"
2. Select "External" as the user type and click "Create"
3. Fill in the required information:
   - App name: "Own My Calendar"
   - User support email: Your email address
   - Developer contact information: Your email address
4. Click "Save and Continue"
5. Add the following scopes:
   - `https://www.googleapis.com/auth/calendar.readonly`
6. Click "Save and Continue"
7. Add test users (your email address)
8. Click "Save and Continue"

## Step 4: Create OAuth 2.0 Credentials
1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Select "Web application" as the application type
4. Name: "Own My Calendar Web Client"
5. Add authorized JavaScript origins:
   - `http://localhost:8000` (for local development)
   - Your production domain (if deployed)
6. Add authorized redirect URIs:
   - `http://localhost:8000/api/google/callback` (for local development)
   - `https://your-domain.com/api/google/callback` (for production)
7. Click "Create"
8. Download the JSON file containing your credentials

## Step 5: Configure the Application
1. Rename the downloaded JSON file to `credentials.json`
2. Place the file in the `storage/app/google-calendar/` directory of your Own My Calendar application
3. Ensure the file has the correct permissions:
   ```
   chmod 600 storage/app/google-calendar/credentials.json
   ```

## Step 6: Test the Integration
1. Log in to your Own My Calendar application
2. Navigate to the Calendar page
3. Click "Connect Google Calendar"
4. Follow the prompts to authorize the application

## Troubleshooting
- If you encounter "Invalid Redirect URI" errors, ensure the redirect URI in your Google Cloud Console matches exactly with the one used by the application
- For "Access Denied" errors, check that you've enabled the Google Calendar API and configured the OAuth consent screen correctly
- If the application can't find the credentials file, verify its location and permissions

## Security Notes
- Keep your `credentials.json` file secure and never commit it to version control
- In production, consider using environment variables or a secure vault for storing sensitive credentials
