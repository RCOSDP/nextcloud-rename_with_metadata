# Nextcloud App: rename_with_metadata
Nextcloud application for renaminig file with metadata.

# Function Overview
 Nextcloud manages the file metadata information ,related  on file path name.
Renaming the file brokes file and metadata relationship.
This application updates the metadata information when renaming file,
and keep relationship between metadata and file.

#Usage

This application adds preRename/postRename hooks to WebDAV metadata function.

https://docs.nextcloud.com/server/latest/developer_manual/basics/events.html#filesystem-root

Add postRename Hook:

* Create Meta-data handling class called "CustomPropertiesBacked"  to WebDAV Application.
* When rename a file, Metadata information also follows to renamed file.

