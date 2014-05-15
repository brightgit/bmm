/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.allowedContent = true;
	config.htmlEncodeOutput = false;
	//config.entities = false;
	config.entities_greek = false;
	config.entities_latin = false;
	//config.htmlEncodeOutput = false;
	config.entities = false;

   config.filebrowserBrowseUrl = '../inc/libs/kcfinder/browse.php?type=files';
   config.filebrowserImageBrowseUrl = '../inc/libs/kcfinder/browse.php?type=images';
   config.filebrowserFlashBrowseUrl = '../inc/libs/kcfinder/browse.php?type=flash';
   config.filebrowserUploadUrl = '../inc/libs/kcfinder/upload.php?type=files';
   config.filebrowserImageUploadUrl = '../inc/libs/kcfinder/upload.php?type=images';
   config.filebrowserFlashUploadUrl = '../inc/libs/kcfinder/upload.php?type=flash';
};
