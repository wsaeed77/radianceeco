# Reports & Analytics Feature

## Overview
A comprehensive reports and analytics dashboard has been added to track performance, lead status, and various business metrics.

## Accessing Reports
Navigate to **Reports** from the main navigation menu, or visit `/reports`

## Features

### 📊 Real-Time Statistics Cards
- **New Leads (7 Days)**: Count of leads created in the last 7 days with 30-day comparison
- **Activities (7 Days)**: Recent activity count with monthly trends
- **Documents (7 Days)**: Document upload statistics
- **Average Conversion Rate**: Overall conversion rate across all agents

### 📈 Agent Performance
**Chart Type**: Bar Chart (Multi-series)
- **Total Leads**: Overall leads assigned to each agent
- **Completed Leads**: Successfully completed installations
- **Active Leads**: Currently ongoing leads
- **Conversion Rate**: Percentage of completed vs total leads

**Top Performers Panel**: Shows the top 5 agents by completed leads

### 📉 Status Distribution
**Chart Type**: Doughnut Chart
- Visual breakdown of all leads by their current status
- Shows distribution across:
  - Hold
  - Not Possible
  - Need To Visit Property
  - Property Visited
  - Survey Booked
  - Survey Done
  - Data Updated In Google Drive
  - Need To Send Data Match
  - Data Match Sent
  - Need To Book Installation
  - Installation Booked
  - Property Installed
  - Unknown

### 🏢 Team/Stage Distribution
**Chart Type**: Pie Chart
- Distribution of leads across different teams:
  - Rishi Submission
  - Boiler Team
  - Loft Team
  - Radiance Team
  - Anesco
  - Unknown

### 📆 Leads Over Time
**Chart Type**: Line Chart
- Time-series visualization of lead creation
- Displays daily lead count over the selected date range (default: last 6 months)
- Helps identify trends and peak periods

### 🔄 Conversion Funnel
**Chart Type**: Horizontal Bar Chart
- Shows lead progression through the conversion pipeline:
  1. Total Leads
  2. Property Visited
  3. Survey Completed
  4. Data Match Sent
  5. Installation Booked
  6. Installed
- Helps identify bottlenecks in the conversion process

### 📱 Lead Source Analysis
**Chart Type**: Bar Chart
- Breakdown of leads by their source:
  - Online
  - Door Knocking
  - Reference (Client)
  - Reference (Other)
- Helps identify most effective lead generation channels

### 📝 Activity Statistics
**Chart Type**: Doughnut Chart
- Distribution of different activity types:
  - Notes
  - Status Changes
  - Stage Changes
  - File Uploads
  - Visits Booked
  - Documents Requested
  - Called Client
  - Property Visited
  - Survey
  - Installation
  - Data Match Sent
  - Pre Approval Sent
  - Submitted

## Technical Implementation

### Backend Controller
**File**: `app/Http/Controllers/ReportController.php`

Key Methods:
- `index()`: Main report dashboard
- `getAgentPerformance()`: Agent metrics and conversion rates
- `getStatusDistribution()`: Lead status breakdown
- `getStageDistribution()`: Team/stage distribution
- `getSourceAnalysis()`: Lead source analytics
- `getLeadsOverTime()`: Time-series lead data
- `getConversionFunnel()`: Conversion pipeline metrics
- `getActivityStatistics()`: Activity type breakdown
- `getDocumentStatistics()`: Document upload stats
- `getTopPerformers()`: Top 5 performing agents
- `getRecentStatistics()`: Last 7/30 days statistics

### Frontend Component
**File**: `resources/js/Pages/Reports/Index.jsx`

**Libraries Used**:
- Chart.js v4
- react-chartjs-2
- Heroicons for UI icons

### Route
```php
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
```

### Navigation
Updated in `resources/js/Layouts/AppLayout.jsx` to include Reports link

## Future Enhancements

### Suggested Additions:
1. **Date Range Filters**: Allow users to select custom date ranges for reports
2. **Export Functionality**: Export reports as PDF or Excel
3. **Scheduled Reports**: Email reports on a schedule
4. **Comparison Views**: Compare performance across different time periods
5. **Goal Tracking**: Set and track performance goals
6. **Predictive Analytics**: Forecast lead trends
7. **Individual Agent Reports**: Detailed drill-down for each agent
8. **Custom Report Builder**: Allow users to create custom reports
9. **Real-time Updates**: WebSocket-based live updates
10. **Mobile Dashboard**: Optimized mobile view

## Performance Considerations
- All queries use database aggregation for efficiency
- Results are calculated on-demand (consider caching for large datasets)
- Charts use lazy loading and responsive design
- Data is paginated where applicable

## Permissions
Currently accessible to all authenticated users. Consider adding role-based restrictions:
- Admin: Full access to all reports
- Manager: Team-specific reports
- Agent: Personal performance only
- Readonly: View-only access

## Support
For issues or feature requests, contact the development team.

---

**Version**: 1.0  
**Last Updated**: {{ date }}  
**Author**: Development Team

