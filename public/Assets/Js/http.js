var HTTP = function()
{
	this.data = null;
	this.lastsize = 1;
	this.size = 0;
	this.header = null;

	this.connect = function()
	{
		var http = "";

		if(window.ActiveXObject)
		{
			http = new ActiveXObject("XMLHTTP.Microsoft");
		}
		else
		{
			http = new XMLHttpRequest();
		}

		return http;
	};

	this.get = function()
	{
		let p = arguments[0];

		var callback = arguments[1] || false;

		var xhr = this.connect();
		var params = p;
		var data = [];
		data['data'] = null	;

		xhr.open("GET", params, true);
		xhr.setRequestHeader("Content-Type", "text/html");

		if (this.header != null)
		{
			for (var x in this.header)
			{
				xhr.setRequestHeader(x, this.header[x]);
			}

			this.header = null;
		}

		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 2 || xhr.readyState == 4)
			{
				if(xhr.readyState == 4)
				{	
					
					data['text'] = xhr.responseText;
					data['status'] = xhr.status;
					data['url'] = xhr.responseURL;

					var type = xhr.response.substring(0,1);

					if(type == "{" || xhr.response.substring(0,2) == '[{')
					{
						data['data'] = JSON.parse(xhr.response);
					}
					else
					{
						data['data'] = xhr.response;
					}

					if(callback !== false)
					{
						let _data = data;
						let __data = {data : _data.data, text : _data.text, status : _data.status, url : _data.url};
						callback.call(this, __data);
						_data = null; __data = null;
					}
				}
			}
		};
		
		xhr.send(null);


		if(callback == false)
		{
			var then = setInterval(function(){
				if(data.data != null)
				{
					clearInterval(then);
					this.data = data;
					data = null;
				}
			},100);
		}

		return this;
	};

	this.post = function()
	{
		"use strict";

		let p = arguments[0];
		let post = arguments[1];

		var callback = arguments[2] || false;
		var method = arguments[3] || 'POST';

		var xhr = this.connect();
		var params = p;
		var data = [];
		data['data'] = null;

		xhr.open(method, params, true);
		xhr.setRequestHeader("content-type", "application/form-encoded");

		if (this.header != null)
		{
			for (var x in this.header)
			{
				xhr.setRequestHeader(x, this.header[x]);
			}
		}

		xhr.onreadystatechange = function()
		{
			if(xhr.readyState == 2 || xhr.readyState == 4)
			{
				if(xhr.readyState == 4)
				{	
					
					data['text'] = xhr.responseText;
					data['status'] = xhr.status;
					data['url'] = xhr.responseURL;

					var type = xhr.response.substring(0,1);

					if(type == "{" || xhr.response.substring(0,2) == '[{')
					{
						data['data'] = JSON.parse(xhr.response);
					}
					else
					{
						data['data'] = xhr.response;
					}

					if(callback !== false)
					{
						let _data = data;
						let __data = {data : _data.data, text : _data.text, status : _data.status, url : _data.url};
						callback.call(this, __data);
						_data = null; __data = null;
					}
				}
			}
		};

		xhr.send(post);


		if(callback == false)
		{
			var then = setInterval(function(){
				if(data.data != null)
				{
					clearInterval(then);
					this.data = data;
					data = null;
				}
			},100);
		}

		return this;
	};

	this.method = function(meth)
	{
		meth = meth.toUpperCase();

		if (meth == 'GET')
		{
			let p = arguments[1];
			var callback = arguments[2] || false;
			return this.get(p, callback);
		}
		else if (meth == 'POST')
		{
			let p = arguments[1];
			let post = arguments[2];

			var callback = arguments[3] || false;
			return this.post(p, post, callback);
		}
		else
		{
			let p = arguments[1];
			let post = arguments[2];

			var callback = arguments[3] || false;
			return this.post(p, post, callback, meth);
		}
	}

	this.then = function(callback)
	{
		var response = setInterval(function(){

			if(typeof this.data != "undefined")
			{
				clearInterval(response);

				if(typeof callback == "function")
				{
					let _data = this.data;
					let data = {data : _data.data, text : _data.text, status : _data.status, url : _data.url};
					callback.call(this, data);
					data = null; __data = null; _data = null;
				}
			}
		},100);
	};

	this.listen = function($for, callback)
	{
		var href = location.href;

		var reg = new RegExp($for, 'gi');

		if (href.match(reg))
		{
			var ind = href.indexOf($for);
			var str = href.substring(ind);

			callback.call(this, str.split('/'));
		}
		else
		{
			callback.call(this, []);
		}
		
	};
};

var $http = new HTTP();