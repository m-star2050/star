# Role Differences: Admin vs Manager vs Executive

## Quick Summary

| Role | Data Access | Delete | Export | Manage Users | Key Feature |
|------|-------------|--------|--------|--------------|-------------|
| **Admin** | ✅ Everything | ✅ Yes | ✅ Yes | ✅ Yes | Can manage user roles |
| **Manager** | ✅ Everything | ✅ Yes | ✅ Yes | ❌ No | Full team access |
| **Executive** | ✅ Only assigned | ❌ No | ❌ No | ❌ No | Limited to own data |

---

# Manager vs Executive: Key Differences

## Overview

The main difference between **Manager** and **Executive** roles is:

- **Manager**: Can see and manage ALL team data (no filtering)
- **Executive**: Can ONLY see records assigned to them (filtered view)

---

## Detailed Comparison

### 1. Data Visibility (What They Can See)

#### Manager
- ✅ Sees **ALL contacts, leads, tasks, deals, files** in the system
- ✅ No filtering applied - sees everything
- ✅ Can view all reports with complete data

#### Executive
- ✅ Sees **ONLY contacts/leads/tasks/deals assigned to them**
- ✅ Filtered automatically - only sees records where:
  - `assigned_user_id` = their user ID, OR
  - `owner_user_id` = their user ID (for deals), OR
  - `uploaded_by` = their user ID (for files)
- ✅ Can view reports but only sees their own data

**Code Implementation:**
```php
// In PermissionHelper::filterByRole()
// Manager:
if (self::isManager($user)) {
    return $query; // No filtering - sees everything
}

// Executive:
if (self::isExecutive($user)) {
    return $query->where('assigned_user_id', $userId); // Only assigned records
}
```

---

### 2. Permissions (What They Can Do)

#### Manager
- ✅ **View**: All contacts, leads, tasks, deals, reports, files
- ✅ **Create**: Can create new records
- ✅ **Edit**: Can edit any record
- ✅ **Delete**: Can delete any record (has delete permissions)
- ✅ **Export**: Can export all data (has export permissions)
- ✅ **Reports**: Can view and export all reports

#### Executive
- ✅ **View**: Only assigned records
- ✅ **Create**: Can create new records
- ✅ **Edit**: Can only edit records assigned to them
- ❌ **Delete**: **CANNOT delete** (no delete permissions)
- ❌ **Export**: **CANNOT export** (no export permissions)
- ✅ **Reports**: Can view reports (but only sees their own data)
- ❌ **Bulk Delete**: Cannot use bulk delete feature

**Permissions Summary:**

| Permission | Manager | Executive |
|------------|---------|-----------|
| view contacts | ✅ | ✅ (only assigned) |
| create contacts | ✅ | ✅ |
| edit contacts | ✅ | ✅ (only assigned) |
| delete contacts | ✅ | ❌ |
| export contacts | ✅ | ❌ |
| view leads | ✅ | ✅ (only assigned) |
| create leads | ✅ | ✅ |
| edit leads | ✅ | ✅ (only assigned) |
| delete leads | ✅ | ❌ |
| export leads | ✅ | ❌ |
| view tasks | ✅ | ✅ (only assigned) |
| create tasks | ✅ | ✅ |
| edit tasks | ✅ | ✅ (only assigned) |
| delete tasks | ✅ | ❌ |
| export tasks | ✅ | ❌ |
| view pipeline | ✅ | ✅ (only assigned) |
| create pipeline | ✅ | ✅ |
| edit pipeline | ✅ | ✅ (only assigned) |
| delete pipeline | ✅ | ❌ |
| export pipeline | ✅ | ❌ |
| view reports | ✅ | ✅ (only their data) |
| export reports | ✅ | ❌ |
| view files | ✅ | ✅ (only their files) |
| upload files | ✅ | ✅ |
| delete files | ✅ | ❌ |

---

### 3. UI Differences (What They See)

#### Manager
- ✅ Sees "Delete Selected" button (bulk delete)
- ✅ Sees "Export" button on all pages
- ✅ Sees all records in DataTables
- ✅ Sees all users in dropdown filters
- ✅ Can filter by any user

#### Executive
- ❌ Does NOT see "Delete Selected" button
- ❌ Does NOT see "Export" button
- ✅ Sees only their assigned records in DataTables
- ✅ Can only see their own records
- ✅ Cannot filter by other users (only sees their own data)

**Code Implementation in Views:**
```blade
@if(auth()->check() && auth()->user()->can('delete contacts'))
    <button>Delete Selected</button>
@endif

@if(auth()->check() && auth()->user()->can('export contacts'))
    <button>Export</button>
@endif
```

---

### 4. Record Access Control

#### Manager
- ✅ Can access, edit, and delete **ANY record**
- ✅ No restrictions on record ownership
- ✅ `PermissionHelper::canAccessRecord()` always returns `true` for Managers

#### Executive
- ✅ Can only access records where:
  - `assigned_user_id` = their user ID, OR
  - `owner_user_id` = their user ID (for deals), OR
  - `uploaded_by` = their user ID (for files)
- ❌ Cannot access records assigned to other users
- ✅ `PermissionHelper::canAccessRecord()` checks ownership before allowing access

**Code Implementation:**
```php
// In PermissionHelper::canAccessRecord()
// Manager:
if (self::isManager($user)) {
    return true; // Can access everything
}

// Executive:
if (self::isExecutive($user)) {
    // Only if assigned to them
    return $record->assigned_user_id == $user->id;
}
```

---

## Real-World Examples

### Example 1: Contacts List

**Scenario**: There are 100 contacts in the system
- 50 assigned to User A (Executive)
- 30 assigned to User B (Executive)
- 20 assigned to User C (Manager)

**What Manager (User C) sees:**
- ✅ All 100 contacts
- ✅ Can edit/delete any contact
- ✅ Can export all 100 contacts
- ✅ Can use bulk delete

**What Executive (User A) sees:**
- ✅ Only 50 contacts (assigned to them)
- ✅ Can edit only those 50 contacts
- ❌ Cannot delete any contacts
- ❌ Cannot export contacts
- ❌ Cannot see contacts assigned to User B

### Example 2: Pipeline/Deals

**Scenario**: There are 20 deals in the system
- 5 deals owned by User A (Executive)
- 10 deals owned by User B (Executive)
- 5 deals owned by User C (Manager)

**What Manager (User C) sees:**
- ✅ All 20 deals in Kanban board
- ✅ Can move any deal between stages
- ✅ Can edit/delete any deal
- ✅ Can export all deals

**What Executive (User A) sees:**
- ✅ Only 5 deals (owned by them)
- ✅ Can move only their 5 deals between stages
- ✅ Can edit only their 5 deals
- ❌ Cannot delete deals
- ❌ Cannot export deals
- ❌ Cannot see deals owned by User B

### Example 3: Reports

**Scenario**: Generating a sales report

**What Manager sees:**
- ✅ Total Contacts: 100
- ✅ Total Leads: 50
- ✅ Total Deals: 20
- ✅ Total Revenue: $500,000
- ✅ Can export full report

**What Executive sees:**
- ✅ Total Contacts: 50 (only their contacts)
- ✅ Total Leads: 10 (only their leads)
- ✅ Total Deals: 5 (only their deals)
- ✅ Total Revenue: $125,000 (only their revenue)
- ❌ Cannot export report

---

## Use Cases

### When to Use Manager Role
- Team leaders who need to oversee all team activities
- Sales managers who need to see all deals and export reports
- Supervisors who need to manage and delete records across the team
- People who need to export data for analysis

### When to Use Executive Role
- Sales representatives who only work with their own leads/deals
- Customer service agents who only handle assigned cases
- Field agents who only see their assigned territories
- Users who should not have export/delete capabilities
- Team members who should focus on their own work only

---

## Summary

| Feature | Manager | Executive |
|---------|---------|-----------|
| **Data Scope** | All records | Only assigned records |
| **View** | Everything | Filtered view |
| **Create** | ✅ Yes | ✅ Yes |
| **Edit** | ✅ Any record | ✅ Only assigned |
| **Delete** | ✅ Yes | ❌ No |
| **Export** | ✅ Yes | ❌ No |
| **Bulk Delete** | ✅ Yes | ❌ No |
| **Reports** | ✅ Full data | ✅ Own data only |
| **Use Case** | Team management | Individual contributor |

---

## Technical Implementation

The differentiation happens in two places:

1. **Query Filtering** (`PermissionHelper::filterByRole()`):
   - Manager: No filter applied
   - Executive: `WHERE assigned_user_id = user_id`

2. **Permission Checks** (Controllers):
   - Manager: Has `delete` and `export` permissions
   - Executive: Does NOT have `delete` and `export` permissions

3. **Record Access** (`PermissionHelper::canAccessRecord()`):
   - Manager: Always returns `true`
   - Executive: Checks if record is assigned to user

This ensures that Executives can only see and work with their own data, while Managers have full team visibility and management capabilities.

