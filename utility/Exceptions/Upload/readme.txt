# Custom UploadException Handler
# Avaliable list of functions

========================================================
= functions [filename can also have a sub path eg. image_upload(tmp_name, icons/filename)]
========================================================

 * image_upload(tmp_name, filename)
 
 * css_upload(tmp_name, filename)
 
 * media_upload(tmp_name, filename)
 
 * js_upload(tmp_name, filename)
 
 * local_upload(tmp_name, filename) // default is root folder, you can push file to a sub folder also eg. api/filename
 
 * remote_upload(filename or array or object, options) // upload a file using client url. options => curl options
 
 eg $options = [
		'userpwd' => 'hello:moorexa'
	];
 				// and many more..
