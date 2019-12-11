angular.module('FrcPortal')
.filter('capitalizeFirst', function() {
	return function(input) {
		return (input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
	}
})
.filter('capitalizeWordsFirst', function() {
	return function(input) {
			if (input !== null) {
			return input.replace(/\w\S*/g, function(txt) {
				return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
			});
		}
	}
})
.filter('sprintf', function() {
	function parse(str, args) {
		var i = 0;
		return str.replace(/%s/g, function() { return args[i++] || '';});
	}
	return function() {
		return parse(Array.prototype.slice.call(arguments, 0,1)[0], Array.prototype.slice.call(arguments, 1));
	}
})
.filter('removeUnderScore', function() {
	return function(input) {
		var str = input.replace(/_/g, ' ');
		return str
	}
})
.filter('htmlTrusted', ['$sce', function($sce){
	return function(textString) {
		return $sce.trustAsHtml(textString);
	};
}])
.filter('tel', function () {
    return function (tel) {
        //console.log(tel);
        if (!tel) { return ''; }

        var value = tel.toString().trim().replace(/^\+/, '');

        if (value.match(/[^0-9]/)) {
            return tel;
        }

        var country, city, number;

         switch (value.length) {
            case 1:
            case 2:
            case 3:
                city = value;
                break;
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                city = value.slice(0, 3);
                number = value.slice(3);
                break;
            case 11:
                country = value.slice(0,1);
                city = value.slice(1, 4);
                number = value.slice(4);
                break;
            case 12:
                country = value.slice(0,2);
                city = value.slice(2, 5);
                number = value.slice(5);
                break;
           default:
                country = value.slice(0,2);
                city = value.slice(2, 5);
                number = value.slice(5);
                break;
        }

        if(number){
            if(number.length>3){
                number = number.slice(0, 3) + '-' + number.slice(3,7);
            }
            else{
                number = number;
            }
            if(country)
            {
                return (country+" (" + city + ") " + number).trim();
            }
            else
            {
                 return (" (" + city + ") " + number).trim();
            }

        }
        else{
            return "(" + city;
        }
    };
})
.filter('secondsToDateTime', [function() {
    return function(seconds) {
        return new Date(1970, 0, 1).setSeconds(seconds);
    };
}])
.filter('teamKeyToNum', [function() {
    return function(key) {
        return key.substring(3);
    };
}])
.filter("emptyToEnd", function () {
    return function (array, key) {
        var present = array.filter(function (item) {
            return item[key];
        });
        var empty = array.filter(function (item) {
            return !item[key]
        });
        return present.concat(empty);
    };
})
.filter('parseUrlFilter', function () {
    var urlPattern = /(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/gi;
    return function (text, target) {
        return text != undefined ? text.replace(urlPattern, '<a target="' + target + '" href="$&">$&</a>') : '';
    };
})
.filter('underscoreless', function () {
  return function (input) {
      return input.replace(/_/g, ' ');
  };
});
