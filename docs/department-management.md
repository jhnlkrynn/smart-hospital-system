# Department Management

Phase 4 implements administrative department management.

## Fields

Departments store `code`, `name`, `description`, `location`, `contact_number`, `email`, nullable `department_head_employee_id`, `status`, creator/updater references, timestamps, and soft deletes.

`code` and `name` are unique. Codes are normalized to uppercase. Status values come from `App\Enums\DepartmentStatus`: `active` and `inactive`.

## Relationships

- Department belongs to a department-head Employee.
- Department has many Employees.
- Department belongs to created-by and updated-by Users.

The department-head foreign key is added after the employees table to avoid circular migration problems.

## Workflow

- Super Admin and Hospital Admin can list, create, view, update, archive, and restore departments.
- Departments with active employees cannot be archived.
- Archiving soft-deletes the department and marks it inactive.
- Restoring reactivates the department.
- Archived departments are preserved for historical employee relationships.

## Routes

| Method | URI | Name |
| --- | --- | --- |
| GET | `/admin/departments` | `admin.departments.index` |
| GET | `/admin/departments/create` | `admin.departments.create` |
| POST | `/admin/departments` | `admin.departments.store` |
| GET | `/admin/departments/{department}` | `admin.departments.show` |
| GET | `/admin/departments/{department}/edit` | `admin.departments.edit` |
| PUT/PATCH | `/admin/departments/{department}` | `admin.departments.update` |
| DELETE | `/admin/departments/{department}` | `admin.departments.destroy` |
| PATCH | `/admin/departments/{department}/restore` | `admin.departments.restore` |

## Searching and Filtering

The index supports search by code, name, location, and department-head name. Filters include status and archived visibility. Sorting is restricted to approved fields to avoid unsafe dynamic SQL.

## Seeded Departments

Seeded fictional departments: ADM, GEN, PED, CAR, DER, ORT, OBG, ER, LAB, PHA, BIL, and RAD.
