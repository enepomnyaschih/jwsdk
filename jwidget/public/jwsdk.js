var JWSDK = (function() {
	var packages = {},
	    timestamps = {},
	    STATUS_PENDING   = 0,
	    STATUS_PREPARING = 1,
	    STATUS_LOADING   = 2,
	    STATUS_LOADED    = 3;
	
	function getPackage(name)
	{
		return packages[name] = packages[name] || {
			name   : name,
			status : STATUS_PENDING
		};
	}
	
	function attachEl(el, success, error)
	{
		var done = false;
		
		document.getElementsByTagName("body")[0].appendChild(el);
		
		function onFileLoaded()
		{
			done = true;
			success();
		}
		
		function onFileError()
		{
			done = true;
			error();
		}
		
		el.onreadystatechange = function ()
		{
			if (done)
				return;
			
			if (this.readyState !== 'complete' && this.readyState !== 'loaded')
				return;
			
			onFileLoaded();
		}
		
		el.onload = onFileLoaded;
		el.onerror = onFileError;
	}
	
	function attachCss(url, success, error)
	{
		var el = document.createElement("link");
		el.rel = "stylesheet";
		el.type = "text/css";
		el.href = url;
		
		attachEl(el, success, error);
	}
	
	function attachJs(url, success, error)
	{
		var el = document.createElement("script");
		el.charset = "utf-8";
		el.type = "text/javascript";
		el.src = url;
		
		attachEl(el, success, error);
	}
	
	return {
		packageHeader : function(
			config) // [required] Object
		{
			var pack = getPackage(config.name);
			if (pack.status === STATUS_LOADED)
				throw new Error('Package "' + config.name + '" is loaded twice');
			
			pack.status = STATUS_LOADED;
			
			var requires = config.requires || [];
			for (var i = 0, l = requires.length; i < l; ++i)
				getPackage(requires[i]).status = STATUS_LOADED;
			
			var loaders = config.loaders || [];
			for (var i = 0, l = loaders.length; i < l; ++i)
			{
				var loader = loaders[i],
					loaderPackage = getPackage(loader.name);
				
				if (loaderPackage.loadInfo)
					continue;
				
				loaderPackage.loadInfo = {
					requires : loader.requires,
					js       : loader.js,
					css      : loader.css
				};
			}
			
			var packageTimestamps = config.timestamps || {};
			for (var i in packageTimestamps)
				timestamps[i] = packageTimestamps[i];
		},
		
		loadPackage : function(
			name,    // [required] String
			success, // [optional] Function()
			error,   // [optional] Function()
			scope)   // [optional] Object
		{
			var isError = false;
			var callback = function()
			{
				var f = isError ? error : success;
				if (f)
					f.apply(scope || this, arguments);
			}
			
			var pack = getPackage(name);
			if (pack.status === STATUS_PREPARING)
				throw new Error('Dependency loop detected while loading "' + name + '" package');
			
			if (pack.status === STATUS_LOADING)
			{
				pack.callbacks.push(callback);
				return;
			}
			
			if (pack.status === STATUS_LOADED)
			{
				setTimeout(callback, 1);
				return;
			}
			
			var loadInfo = pack.loadInfo;
			if (!loadInfo)
				throw new Error('Package "' + name + '" does not have loading info. Add it to "loaders" list of dependent package configuration');
			
			pack.status = STATUS_PREPARING;
			
			var requires = loadInfo.requires || [];
			for (var i = 0, l = requires.length; i < l; ++i)
				JWSDK.loadPackage(requires[i]);
			
			pack.status = STATUS_LOADING;
			pack.callbacks = [];
			pack.callbacks.push(callback);
			
			var css = loadInfo.css || [];
			var js  = loadInfo.js  || [];
			
			var pendingFiles = css.length + js.length;
			
			function onFileLoaded()
			{
				if (--pendingFiles)
					return;
				
				var callbacks = pack.callbacks.concat();
				for (var i = 0, l = callbacks.length; i < l; ++i)
					callbacks[i]();
			}
			
			function onFileError()
			{
				isError = true;
				onFileLoaded();
			}
			
			for (var i = 0, l = css.length; i < l; ++i)
				attachCss(css[i], onFileLoaded, onFileError);
			
			for (var i = 0, l = js.length; i < l; ++i)
				attachJs(js[i], onFileLoaded, onFileError);
		},
		
		getTimestamp: function(name)
		{
			return timestamps[name];
		}
	};
})();
