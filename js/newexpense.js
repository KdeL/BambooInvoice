function newExpense() {
	$("addexp").setAttribute("href","#newexpense");
	$("createExpense").disabled = true; // disable submit

	allNodes = document.getElementsByClassName("createExpense");
	for(i = 0; i < allNodes.length; i++) {
		allNodes[i].onclick = function() {
			Effect.toggle ('newexpense', 'Blind', {duration: 0.4});
			this.setAttribute ('href', '#');
			//if ($('overduenotice')) {
			//	$('overduenotice').style.display = "none";	  
			//}
		}
	}

	$("newexpensecancel").onclick = function() {
		Effect.toggle ('newexpense', 'Blind', {duration:0.4});
	}
	$("vendor_id").onchange = function() {
		if ($F("vendor_id") != 0) {
			$("createExpense").disabled = false;
			$("newVendor").value = "";
		} else {
			$("createExpense").disabled = true;
		}
	}
	$("newVendor").onkeyup = function() {
		if ($F("newVendor")=="") {
			$("createExpense").disabled = true;
			$("vendor_id").selectedIndex = 0;
		} else {
			$("createExpense").disabled = false;
			$("vendor_id").selectedIndex = 0;
		}
	}
}

addEvent(window,'load',newExpense);