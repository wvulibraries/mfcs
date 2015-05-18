
# MFCS -- Metadata Form Creation System

MFCS is distributed under the WVU Open Source License. 

The Metadata Form Creation System (MFCS) is WVU Libraries answer for providing an easy to use interface for librarians, staff, and students for entering metadata and uploading digital items for our digital collections. MFCS is also our archival and preservation system. 

MFCS is a delivery and repository agnostic system. 

MFCS should be able to export data in any format for, or connect to, any digital project system (Hydra, DLXS, Islandora, etc ... ). Custom programming is required to export or connect to your public facing repository systems. 

The ultimate design goal for MFCS is to provide institutions with a robust system to store and archive digital projects, finding aids, and other material that will ultimately live in a system such as Hydra, Islandora, or DLXS (or any other) without having to worry about Lock-in or complex upgrade paths between digital repository systems. As systems change, become obsolete, or are updated the only change that should be needed to migrate data to a new system are new or updated MFCS export scripts. No more exporting and importing data between your old authoritative system and your new one, which reduces the risk of cross walking errors and data corruption. 

A vagrant setup is provided as part of the repository. For authentication into the vagrant box a simple script is located here:
[http://localhost:8080/vagrantLogin.php](http://localhost:8080/vagrantLogin.php)

Video 1, Metadata Entry Demo:
http://www.youtube.com/watch?v=8RVqZNPsf8A

Video 2, Form Building Demo:
http://www.youtube.com/watch?v=9JB00pXZWWw

### Features

1. Form Builder
1. Metadata Entry Forms
	* Drag and Drop uploading of digital files
	* Revision Control
	* Controlled Vocabulary
1. Form Level Permissions
	* View Only
	* Editor
	* Administrators
1. System level permissions
	* Student
	* Staff
	* Librarian
	* Systems Administrator
1. Watermark Management
1. Object Searching
1. Statistics
1. exporting to any digital project system
1. Many more ... 

## Form Builder

The heart of MFCS is the form builder. The Form Builder allows metadata librarians and adminsitrators to create forms by dragging and dropping fields and then defining the behaver of those form fields via a web interface. No programming required. 

Forms can be nested, so that pages can be part of books, or folders in boxes. Forms can also be linked, so that a centralized vocabulary is possible (either for a specific form or across forms and projects). 

Form fields can be any type valid HTML 5 form field. Additionally custom validation is possible using built in checks or custom regular expressions for more advanced pattern matching (e.g. /\d\d\d\d((-\d\d)?-\d\d)?/  to match and accept dates in the "YYYY" or "YYYY-MM" or "YYYY-MM-DD" formats)

Upload fields can be configured with a large set of options to that the original upload file can be retained as well as exporting options (resize/convert image formats. create thumb nails, combine tiffs into a single OCR pdf, add borders, watermarts, etc ... )

## Metadata Entry

Students, Librarians, and Staff use the forms created in the form builder to enter metadata for digital collections. 

### Revision Control

All objects in the system have revision control. All previous revisions on an object can be viewed and you can revert to any previous revision. Digital files are never overwritten or deleted, so previous versions of digital files can be viewed as well. 

### Uploading Digital Objects / files

Uploading files is as simple as dragging and dropping the files to the web page. MFCS has been tested with uploading more than 1GB of data via the forms for a single object. Forms can be configured to process the uploads immediately or place them in a queue where they can be processed by nightly cron jobs. 

Forms that will have very large digital objects should have the digital objects side-loaded from the server. 

#### Supported file types

Currently only image files are supported. WVU Libraries digital projects / collections currently only work with TIFF formatted images. As the need for processing audio and video arises we will expand the functionality and supported file types. 

If you need other file type support sooner, you are encouraged to fork and submit pull requests. 

## Archival System

WVU Libraries is in the process of migrating our digital collections from stand-alone metadata systems to MFCS. 

Digital objects are never deleted or over written in the system. Each time that digital file is uploaded to the system it is giving a unique ID and the old ID is saved in the revision control. 

## Terminology

### Objects

Each object is a single item in a collection. 

### Forms : The forms that are created to collect metadata.

#### Object Forms

The object forms are where objects are created. 

Object forms can be very broadly defined or narrowly. An Object form can be used in a single project or across multiple projects. 

#### Metadata Forms

Metadata forms are where controlled vocabulary forms are created. These are the Subject Headings or other pieces of information about objects that will be reused over and over.

* Personal Names
* Corporate Names
* Locations
* Medium
* etc ...

Generally these have 1 single text field, but can have as many pieces of information as are needed. 

These forms are linked to object forms as either drop down select fields or multi select fields. 

These forms can belong to a single object form or multiple object forms if the same vocabulary is needed across different forms. 

#### Projects

Projects are essentially a collection of objects. These objects can span multiple forms and be pulled together as part of an export script. You don't have to use projects if you have specific forms for each of your projects. However, if you have different types of objects in a single project, for example EAD finding aids and images, a project brings all of the different objects created using different forms together.


# Installing 3rd Party Plugins

Requires EngineAPI 3.2
https://github.com/wvulibraries/engineAPI/tree/engineAPI-3.x

See bootstrap.sh for up to date examples of installing all of the third party software. 

