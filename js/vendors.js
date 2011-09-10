// Accordian effect
function accordianInit() {
	accorianVendors = document.getElementsByClassName('displayLink');
	for (i=0; i<accorianVendors.length; i++) {
		accorianVendors[i].onclick = function() {
			Effect.toggle ('vendorInfo'+this.getAttribute('id').substr(6), 'Blind', {duration:'0.4'});
		}
	}
}

function contactLinkInit() {
	contactLinks = document.getElementsByClassName('addcontact');
	for (i=0; i<contactLinks.length; i++) {
		contactLinks[i].setAttribute ('href', 'javascript:void(0);');
		contactLinks[i].onclick = function() {
			vendorContactEntry (this.getAttribute('id'));
		}
	}
	
	$('close').onclick = function() {
		new Effect.Fade('vendorContactEntry', {duration:0.6});
	}
}

function strip_tags (str) {
	return str.replace(/(<([^>]+)>)/ig,""); 
}

function vendorContactEntry (id) {
	// the following line gets the name of the company they're about to add a contact to	
	company_name = strip_tags($(id).innerHTML);
	$('company_nameContact').innerHTML = company_name;
	$('vendor_contact_id').value = id.substr(11);
	new Effect.Appear('vendorContactEntry', {duration:0.6});
	new Draggable('vendorContactEntry',{revert:false});
	Field.focus('first_name')
}

function checkVendor() {
	if($F('zip').length == 5) {
		var url = 'checkZip.cfm';
		var params = 'zip=' + $F('zip');
	}
}

function ajaxAddContact() {
	if ($F('first_name') != '' && $F('last_name') != '' && checkMail($F('email'))) {
		new Ajax.Request(base_url+'vendorcontacts/add', {postBody: 'vendor_id='+$F('vendor_contact_id')+'&'+Form.serialize($('vendorcontact')), onComplete: addContact});	
	} else {
		$('ajaxstatus').innerHTML = lang_vendors_contact_add;
	}
}

function addContact (response) {
	// if there is a no contact notice, remove it
	if ($('nocontact'+$('vendor_contact_id').value)) {
		$('nocontact'+$('vendor_contact_id').value).style.display = 'none';
	}
	// add to list
	newVendorTable = '<table id="vendorTable' + response.responseText + '">';
	newVendorTable += '<tr class="contactname"><td>' + $F('first_name') + ' ' + $F('last_name') + '</td>';
	newVendorTable += '<td class="vendoreditdelete">';
	newVendorTable += '<td class="vendoreditdelete"><a href="'+base_url+'vendorcontacts/edit/';
	newVendorTable += response.responseText + '">'+lang_edit+'</a> | ';
	newVendorTable += ' <a href="javascript:void(0);" onclick="ajaxDeleteContact (this.getAttribute(\'id\'));"';
	newVendorTable += '" class="ajaxDelContact" id="_' + response.responseText + '">'+lang_delete+'</a></td>';
	newVendorTable += '</tr><tr><td colspan="2"><a href="mailto:';
	newVendorTable += $F('email') + '">' + $F('email') + '</a><br />' + $F('phone') + '</td></tr></table>';
	// hide and clear the form
	new Insertion.Bottom ('contactList'+$('vendor_contact_id').value, newVendorTable);
	new Effect.Highlight ('vendorTable' + response.responseText, {startcolor:'#F7E47D', endcolor:'#FFFFFF'});
	new Effect.Fade('vendorContactEntry', {duration:0.6});
	setTimeout ('clearForm()', 600);
}

function clearForm() {
	// blank out form
	$('first_name').value = '';
	$('last_name').value = '';
	$('email').value = '';
	$('phone').value = '';
	$('ajaxstatus').innerHTML = '';	
}

function ajaxDeleteContact(id) {
	new Ajax.Updater('ajaxFeedback',base_url+'/vendorcontacts/delete/', {postBody: 'id='+id.substr(1), onSuccess: deleteContact(id.substr(1))});
}

function deleteContact (id) {
	new Effect.Highlight ('vendorTable'+id, {startcolor:'#F7E47D', endcolor:'#FFFFFF'});
	Effect.Fade ('vendorTable'+id);
}

function deleteContactInit() {
	deleteContactLinks = document.getElementsByClassName('ajaxDelContact');
	for (i=0; i<deleteContactLinks.length; i++) {
		deleteContactLinks[i].setAttribute ('href', 'javascript:void(0)');
		deleteContactLinks[i].onclick = function() {
			ajaxDeleteContact (this.getAttribute("id"));
		}
	}
}

addEvent (window, "load", deleteContactInit);
addEvent (window, "load", accordianInit);
addEvent (window, "load", contactLinkInit);