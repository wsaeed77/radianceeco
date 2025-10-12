# EPC API Setup Guide

This guide explains how to set up the EPC (Energy Performance Certificate) API integration for the Radiance Eco CRM.

## Overview

The EPC API integration allows you to automatically fetch Energy Performance Certificates from the UK Government's Open Data Communities platform based on a property's postcode and address.

## Features

- ✅ Fetch EPC certificates by postcode and door number
- ✅ Display comprehensive energy ratings (A-G)
- ✅ Show detailed property features breakdown
- ✅ Display energy costs (current and potential)
- ✅ Environmental impact information (CO2 emissions)
- ✅ Property construction details
- ✅ Certificate history and dates

## Getting an API Key

### 1. Register for an API Key

The UK Government provides free access to the EPC API through the Open Data Communities portal.

1. Visit: https://epc.opendatacommunities.org/
2. Click on "Login/Register" at the top right
3. Create a new account or login with existing credentials
4. Once logged in, navigate to "API Access"
5. Request an API key - you should receive it immediately or within 24 hours via email

### 2. API Key Format

The API key is used with Basic Authentication. You'll receive an email address-like key (e.g., `your-email@example.com:api-key-here`)

## Configuration

### 1. Add to `.env` File

Add the following to your `.env` file:

```env
# EPC API Configuration
EPC_API_URL=https://epc.opendatacommunities.org/api/v1
EPC_API_KEY=your-api-key-here
```

**Important:** Replace `your-api-key-here` with your actual API key received from the Open Data Communities portal.

### 2. Clear Configuration Cache

After updating the `.env` file, clear your configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
```

## Usage

### Fetching EPC Data

1. **Navigate to a Lead:** Go to any lead's detail page
2. **Scroll to EPC Section:** The EPC section appears after the Eligibility Details section
3. **Click "Fetch EPC Report":** Click the button in the header of the EPC section
4. **View Results:** The system will automatically fetch and display the EPC data

### What Data is Fetched

The integration retrieves and displays:

#### Energy Rating Overview
- Current energy rating (A-G scale)
- Current energy efficiency score (0-100)
- Potential energy rating
- Potential energy efficiency score
- Property type and floor area
- Construction age band

#### Property Features
- **Walls:** Description and energy efficiency rating
- **Roof:** Description and energy efficiency rating
- **Windows:** Description and energy efficiency rating
- **Main Heating:** Description and energy efficiency rating
- **Heating Controls:** Description and energy efficiency rating
- **Hot Water:** Description and energy efficiency rating
- **Lighting:** Description and energy efficiency rating
- **Floor:** Description and energy efficiency rating
- **Secondary Heating:** If applicable

Each feature is rated from "Very Good" to "Very Poor" based on energy efficiency.

#### Energy Costs
- Current annual costs for:
  - Heating
  - Hot Water
  - Lighting
- Potential costs after improvements

#### Environmental Impact
- Current CO2 emissions (kg/year)
- Potential CO2 emissions after improvements
- Environmental impact ratings

#### Certificate Information
- Certificate number (LMK key)
- Lodgement date
- Inspection date
- Property tenure
- Local authority

## Database Storage

EPC data is stored in the `leads` table with the following fields:

- `epc_data` (JSON): Complete EPC certificate data
- `epc_rating` (String): Current energy rating (for quick reference)
- `epc_details` (Text): Additional EPC details
- `epc_fetched_at` (Timestamp): When the data was last fetched

## API Endpoints

### Fetch EPC for Lead
```
POST /leads/{lead}/epc/fetch
```
Fetches EPC certificate from the API and stores it in the database.

### Clear EPC Data
```
DELETE /leads/{lead}/epc
```
Clears stored EPC data for a lead.

## Troubleshooting

### No EPC Found

**Possible Reasons:**
1. The postcode is incorrect or not in the correct format
2. The address doesn't have an EPC certificate on record
3. The property is newly built and hasn't been assessed yet
4. The certificate has expired (older than 10 years)

**Solutions:**
- Verify the postcode is correct
- Try fetching with just the postcode (without specific address)
- Check manually on the EPC register: https://www.epcregister.com/

### API Key Errors

**Error: "Authorization Required" or "Invalid API Key"**

**Solutions:**
1. Verify your API key is correctly added to `.env`
2. Make sure there are no extra spaces or quotes
3. Clear configuration cache: `php artisan config:clear`
4. Test your API key directly using a tool like Postman

### Rate Limiting

The UK Government EPC API has rate limits. If you're making too many requests:

**Solutions:**
- Space out your requests
- Cache EPC data (already implemented - data is stored in database)
- Contact the API provider for higher limits if needed

## Data Privacy & GDPR

EPC certificates are public records in the UK. The data fetched includes:
- Property address
- Energy efficiency information
- Construction details

**Note:** No personal data about property owners is included in EPC certificates.

## API Documentation

For full API documentation, visit:
https://epc.opendatacommunities.org/docs/api

## Technical Details

### Service Class
`App\Services\EpcApiService`

Key methods:
- `fetchCertificate($postcode, $address)` - Fetch certificate from API
- `formatEpcData($epcData)` - Format API response for storage
- `getEnergyRatingLabel($score)` - Convert score to A-G rating

### Controller
`App\Http\Controllers\EpcController`

Endpoints:
- `fetchForLead(Request $request, Lead $lead)` - Fetch and store EPC
- `clearForLead(Lead $lead)` - Clear stored EPC data

### Frontend Component
`resources/js/Pages/Leads/Show.jsx`

Features:
- Visual energy rating display with color coding
- Comprehensive features table
- Energy costs comparison
- Certificate information display

## Example Request (cURL)

You can test the API directly:

```bash
curl -X GET \
  "https://epc.opendatacommunities.org/api/v1/domestic/search?postcode=SW1A1AA" \
  -H "Accept: application/json" \
  -H "Authorization: Basic $(echo -n 'your-api-key:' | base64)"
```

## Support

For issues with:
- **API Access:** Contact Open Data Communities support
- **Application Integration:** Contact your development team
- **Data Accuracy:** Contact the EPC assessment organization

## Additional Resources

- UK Government EPC Portal: https://www.gov.uk/find-energy-certificate
- EPC Register: https://www.epcregister.com/
- Open Data Communities: https://epc.opendatacommunities.org/

---

Last Updated: October 2025

