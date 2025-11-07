# Admin vs Manager vs Executive: Complete Comparison

## Quick Summary

| Role | Data Access | Delete | Export | Manage Users | Use Case |
|------|-------------|--------|--------|--------------|----------|
| **Admin** | ✅ Everything | ✅ Yes | ✅ Yes | ✅ Yes | System administrators |
| **Manager** | ✅ Everything | ✅ Yes | ✅ Yes | ❌ No | Team leaders |
| **Executive** | ✅ Only assigned | ❌ No | ❌ No | ❌ No | Individual contributors |

---

## Detailed Comparison

### 1. Data Visibility (What They Can See)

#### Admin
- ✅ Sees **ALL records** in the system (no filtering)
- ✅ Sees all contacts, leads, tasks, deals, files
- ✅ Sees all users and their activities
- ✅ Sees complete reports with all data
- ✅ No restrictions on data visibility

#### Manager
- ✅ Sees **ALL records** in the system (no filtering)
- ✅ Sees all contacts, leads, tasks, deals, files
- ✅ Sees all users and their activities
- ✅ Sees complete reports with all data
- ✅ No restrictions on data visibility
- ⚠️ **Same as Admin in terms of data visibility**

#### Executive
- ✅ Sees **ONLY records assigned to them**
- ✅ Sees only contacts/leads/tasks/deals where:
  - `assigned_user_id` = their user ID, OR
  - `owner_user_id` = their user ID (for deals), OR
  - `uploaded_by` = their user ID (for files)
- ✅ Sees reports but only with their own data
- ❌ Cannot see records assigned to other users
- ❌ Cannot see other users' activities

**Visual Example:**

If there are 100 contacts in the system:
- **Admin**: Sees all 100 contacts
- **Manager**: Sees all 100 contacts
- **Executive**: Sees only contacts assigned to them (e.g., 25 contacts)

---

### 2. Permissions Matrix

| Permission | Admin | Manager | Executive |
|------------|-------|---------|-----------|
| **View Contacts** | ✅ All | ✅ All | ✅ Assigned only |
| **Create Contacts** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Edit Contacts** | ✅ Any | ✅ Any | ✅ Assigned only |
| **Delete Contacts** | ✅ Yes | ✅ Yes | ❌ No |
| **Export Contacts** | ✅ Yes | ✅ Yes | ❌ No |
| **View Leads** | ✅ All | ✅ All | ✅ Assigned only |
| **Create Leads** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Edit Leads** | ✅ Any | ✅ Any | ✅ Assigned only |
| **Delete Leads** | ✅ Yes | ✅ Yes | ❌ No |
| **Export Leads** | ✅ Yes | ✅ Yes | ❌ No |
| **View Tasks** | ✅ All | ✅ All | ✅ Assigned only |
| **Create Tasks** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Edit Tasks** | ✅ Any | ✅ Any | ✅ Assigned only |
| **Delete Tasks** | ✅ Yes | ✅ Yes | ❌ No |
| **Export Tasks** | ✅ Yes | ✅ Yes | ❌ No |
| **View Pipeline** | ✅ All | ✅ All | ✅ Assigned only |
| **Create Pipeline** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Edit Pipeline** | ✅ Any | ✅ Any | ✅ Assigned only |
| **Delete Pipeline** | ✅ Yes | ✅ Yes | ❌ No |
| **Export Pipeline** | ✅ Yes | ✅ Yes | ❌ No |
| **View Reports** | ✅ Full data | ✅ Full data | ✅ Own data only |
| **Export Reports** | ✅ Yes | ✅ Yes | ❌ No |
| **View Files** | ✅ All | ✅ All | ✅ Own files only |
| **Upload Files** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Delete Files** | ✅ Yes | ✅ Yes | ❌ No |
| **Manage User Roles** | ✅ Yes | ❌ No | ❌ No |
| **Bulk Delete** | ✅ Yes | ✅ Yes | ❌ No |

---

### 3. Key Differences

#### The ONE Key Difference Between Admin and Manager

**Admin can manage user roles, Manager cannot.**

| Feature | Admin | Manager |
|---------|-------|---------|
| Access `/crm/user-roles` page | ✅ Yes | ❌ No |
| Assign roles to users | ✅ Yes | ❌ No |
| Change user permissions | ✅ Yes | ❌ No |
| See "User Roles" in sidebar | ✅ Yes | ❌ No |

**Everything else is the same between Admin and Manager:**
- Same data visibility (both see everything)
- Same delete permissions (both can delete)
- Same export permissions (both can export)
- Same edit permissions (both can edit any record)

#### The MAJOR Difference Between Manager/Admin and Executive

**Manager/Admin see everything, Executive sees only assigned records.**

| Feature | Admin/Manager | Executive |
|---------|---------------|-----------|
| Data scope | All records | Only assigned records |
| Delete capability | ✅ Yes | ❌ No |
| Export capability | ✅ Yes | ❌ No |
| Bulk operations | ✅ Yes | ❌ No |
| Reports | Full team data | Own data only |

---

### 4. UI Differences (What They See in the Interface)

#### Admin
- ✅ Sees "User Roles" link in sidebar (Administration section)
- ✅ Sees "Delete Selected" button (bulk delete)
- ✅ Sees "Export" button on all pages
- ✅ Sees all records in DataTables
- ✅ Can filter by any user
- ✅ Sees all users in dropdowns

#### Manager
- ❌ Does NOT see "User Roles" link in sidebar
- ✅ Sees "Delete Selected" button (bulk delete)
- ✅ Sees "Export" button on all pages
- ✅ Sees all records in DataTables
- ✅ Can filter by any user
- ✅ Sees all users in dropdowns
- ⚠️ **Same as Admin except no User Roles access**

#### Executive
- ❌ Does NOT see "User Roles" link in sidebar
- ❌ Does NOT see "Delete Selected" button
- ❌ Does NOT see "Export" button
- ✅ Sees only assigned records in DataTables
- ✅ Cannot filter by other users (only sees own data)
- ✅ Only sees records assigned to them

---

### 5. Real-World Examples

#### Example 1: Contacts Management

**Scenario**: There are 100 contacts in the system
- 50 assigned to User A (Executive)
- 30 assigned to User B (Executive)
- 20 assigned to User C (Manager)

**What Admin sees:**
- ✅ All 100 contacts
- ✅ Can edit/delete any contact
- ✅ Can export all 100 contacts
- ✅ Can use bulk delete
- ✅ Can assign contacts to any user
- ✅ Can access User Roles page

**What Manager (User C) sees:**
- ✅ All 100 contacts
- ✅ Can edit/delete any contact
- ✅ Can export all 100 contacts
- ✅ Can use bulk delete
- ✅ Can assign contacts to any user
- ❌ Cannot access User Roles page

**What Executive (User A) sees:**
- ✅ Only 50 contacts (assigned to them)
- ✅ Can edit only those 50 contacts
- ❌ Cannot delete any contacts
- ❌ Cannot export contacts
- ❌ Cannot use bulk delete
- ❌ Cannot see contacts assigned to User B
- ❌ Cannot access User Roles page

#### Example 2: Reports Dashboard

**Scenario**: Generating a sales report with:
- Total Contacts: 100
- Total Leads: 50
- Total Deals: 20
- Total Revenue: $500,000

**What Admin sees:**
- ✅ Total Contacts: 100
- ✅ Total Leads: 50
- ✅ Total Deals: 20
- ✅ Total Revenue: $500,000
- ✅ Can export full report
- ✅ Can see breakdown by user

**What Manager sees:**
- ✅ Total Contacts: 100
- ✅ Total Leads: 50
- ✅ Total Deals: 20
- ✅ Total Revenue: $500,000
- ✅ Can export full report
- ✅ Can see breakdown by user
- ⚠️ **Same as Admin**

**What Executive sees:**
- ✅ Total Contacts: 25 (only their contacts)
- ✅ Total Leads: 10 (only their leads)
- ✅ Total Deals: 5 (only their deals)
- ✅ Total Revenue: $125,000 (only their revenue)
- ❌ Cannot export report
- ❌ Cannot see other users' data

#### Example 3: User Role Management

**Scenario**: Trying to access `/crm/user-roles` page

**Admin:**
- ✅ Can access the page
- ✅ Can see all users and their roles
- ✅ Can change any user's role
- ✅ Sees "User Roles" link in sidebar

**Manager:**
- ❌ Gets "403 Unauthorized" error
- ❌ Cannot access the page
- ❌ Does not see "User Roles" link in sidebar

**Executive:**
- ❌ Gets "403 Unauthorized" error
- ❌ Cannot access the page
- ❌ Does not see "User Roles" link in sidebar

---

### 6. Technical Implementation

#### Query Filtering

```php
// In PermissionHelper::filterByRole()

// Admin: No filtering
if (self::isAdmin($user)) {
    return $query; // Sees everything
}

// Manager: No filtering (same as Admin)
if (self::isManager($user)) {
    return $query; // Sees everything
}

// Executive: Filtered by assignment
if (self::isExecutive($user)) {
    return $query->where('assigned_user_id', $userId); // Only assigned
}
```

#### Permission Checks

```php
// Admin: Has all permissions
$adminRole->givePermissionTo(Permission::all());

// Manager: Has most permissions (except user management)
$managerPermissions = [
    'view contacts', 'create contacts', 'edit contacts', 
    'delete contacts', 'export contacts',
    // ... all other permissions except 'manage all data'
];

// Executive: Limited permissions (no delete/export)
$executivePermissions = [
    'view contacts', 'create contacts', 'edit contacts',
    // No 'delete contacts', no 'export contacts'
];
```

#### Record Access Control

```php
// In PermissionHelper::canAccessRecord()

// Admin: Can access everything
if (self::isAdmin($user)) {
    return true;
}

// Manager: Can access everything (same as Admin)
if (self::isManager($user)) {
    return true;
}

// Executive: Can only access assigned records
if (self::isExecutive($user)) {
    return $record->assigned_user_id == $user->id;
}
```

---

### 7. Use Cases

#### When to Use Admin Role
- System administrators
- IT administrators
- Owners of the business
- People who need to manage user access
- People who need full system control
- **First registered user (automatic)**

#### When to Use Manager Role
- Team leaders who need to oversee all team activities
- Sales managers who need to see all deals and export reports
- Supervisors who need to manage records across the team
- People who need to export data for analysis
- **People who need full data access but NOT user management**

#### When to Use Executive Role
- Sales representatives who only work with their own leads/deals
- Customer service agents who only handle assigned cases
- Field agents who only see their assigned territories
- Team members who should focus on their own work
- **People who should NOT have export/delete capabilities**
- **Subsequent registered users (automatic)**

---

### 8. Visual Hierarchy

```
┌─────────────────────────────────────────┐
│              ADMIN                      │
│  • Sees everything                      │
│  • Can delete/export                    │
│  • Can manage user roles ⭐             │
│  • Full system access                   │
└─────────────────────────────────────────┘
                │
                │ (Same data access)
                ▼
┌─────────────────────────────────────────┐
│            MANAGER                      │
│  • Sees everything                      │
│  • Can delete/export                    │
│  • Cannot manage user roles ❌          │
│  • Full team access                     │
└─────────────────────────────────────────┘
                │
                │ (Different data access)
                ▼
┌─────────────────────────────────────────┐
│           EXECUTIVE                     │
│  • Sees only assigned records           │
│  • Cannot delete/export ❌              │
│  • Cannot manage user roles ❌          │
│  • Limited access                       │
└─────────────────────────────────────────┘
```

---

### 9. Decision Tree: Which Role to Use?

```
Do they need to manage user roles?
├─ YES → Admin
└─ NO
   ├─ Do they need to see ALL team data?
   │  ├─ YES → Manager
   │  └─ NO → Executive
   │
   └─ Do they need delete/export capabilities?
      ├─ YES → Manager
      └─ NO → Executive
```

---

### 10. Summary Table

| Feature | Admin | Manager | Executive |
|---------|-------|---------|-----------|
| **Data Scope** | All records | All records | Only assigned |
| **View** | Everything | Everything | Filtered |
| **Create** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Edit** | ✅ Any | ✅ Any | ✅ Assigned only |
| **Delete** | ✅ Yes | ✅ Yes | ❌ No |
| **Export** | ✅ Yes | ✅ Yes | ❌ No |
| **Bulk Delete** | ✅ Yes | ✅ Yes | ❌ No |
| **Reports** | Full data | Full data | Own data only |
| **Manage Users** | ✅ Yes | ❌ No | ❌ No |
| **User Roles Page** | ✅ Yes | ❌ No | ❌ No |
| **Auto-Assigned** | First user | Manual | Subsequent users |

---

## Key Takeaways

1. **Admin vs Manager**: Only difference is user role management capability
2. **Manager vs Executive**: Major difference in data visibility and delete/export capabilities
3. **Admin vs Executive**: Complete opposite ends of the spectrum
4. **Manager is like Admin** but without user management
5. **Executive is limited** to assigned records only, no delete/export

---

## Quick Reference

- **Admin** = Full system control (including user management)
- **Manager** = Full team management (without user management)
- **Executive** = Individual contributor (assigned records only)

