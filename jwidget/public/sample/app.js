/*
    Sample jWidget SDK based application source file.
    
    Copyright (C) 2012 Egor Nepomnyaschih
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.
    
    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
    If our page is derived from jwidget/build/config/includes/sample.json
    then jQuery and "Output" namespace are guaranteed to be attached.
*/

jQuery(function() {
    /*
        Since we use jwidget/build/config/templates/sample.html page template,
        form for message entering exists already, we just need to show it and
        attach submit handler.
    */
    
    var formEl = jQuery(".message-form");
    
    formEl.show();
    
    formEl.submit(function(event) {
        event.preventDefault();
        
        var message    = jQuery(".message-text").val();
        var outputType = jQuery(".message-type").val();
        
        Output[outputType](message);
    });
    
    jQuery(".message-text").focus();
});
