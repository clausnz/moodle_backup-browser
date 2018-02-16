# Moodle Backup Browser

This Backup Browser add-on for the open-source learning management system Moodle (https://moodle.org) allows tree-based browsing of the Moodle backup-files based by merging data from filesystem and database.

### Features:

* Browsing the Moodle backup-files 
* Multiple search (by course, lecturer, etc.)
* Download option
* Ldap authentification

### Requirements

* PHP >= 5.5.9
* Moodle >= 3.1 (may work with older version)

### Install (development)

Create following database dumps of your Moodle installation and move into directory `dbfiles`

```
mkdir {project_root}/dbfiles
mv \
mdl_course.sql \
mdl_course_categories.sql \
mdl_context.sql \
mdl_role_assignments.sql \
mdl_user.sql \
{project_root}/dbfiles
```

Run `vagrant up` to setup development system.