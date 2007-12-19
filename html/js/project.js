/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/


YAHOO.projectManager = {
	getAjaxProject: function(selectedIn){
		this.selectedDBID = selectedIn;
		var callback = 
		{ 
			start:function(eventType, args){ 
			},
			success: function(o) {
				var response = eval("("+o.responseText+")");
				var domNode = document.getElementById('project-area');
//				YAHOO.log(o.responseText);
				domNode.innerHTML = "";
				for(var i = 0; i < response.length; i++){
					var proj = new project(response[i]['project']);
					domNode.appendChild(proj.createHTML());
				}
				
	//			domNode.innerHTML = o.responseText;
	//			YAHOO.util.Event.onAvailable("project-choices",setupSelectProjectCB);
			},
			failure: function(o) {
				YAHOO.log('failed!');
			} 
		} 
		YAHOO.util.Connect.asyncRequest('GET', "callback/getProjects.php", callback, null); 
	},

	getSelectedDBID: function(){
		return this.selectedDBID;
	},
	getSelected: function(){
		return this.selected;
	},
	
	updateSelected: function(selec){
		if(this.selected){
			this.selected.unselect();
		}
		this.selected = selec;
		this.selected.selected();
	}
};




function project(projectIn){
	project.superclass.constructor.call();
	this.project = projectIn;
	this.initSelectable();
}
YAHOO.extend(project,selectable);
project.prototype.isSelected = function(){
 return (this == YAHOO.projectManager.selected);
}


project.prototype.clicked = function(e){
	YAHOO.util.Event.stopEvent(e);
	var callback = 
	{ 
		start:function(eventType, args){ 
		},
		success: function(o) {
			YAHOO.versionManager.getAjaxVersions();
		},
		failure: function(o) {
			YAHOO.log('failed!');
		} 
	} 
	YAHOO.projectManager.updateSelected(this);
	YAHOO.util.Connect.asyncRequest('POST', "callback/setCurrentProject.php", callback, "project="+this.project);	
}
project.prototype.createHTML = function(){
	this.domElem = document.createElement("li");
	this.domElem.innerHTML = this.project;
	this.addEvents();

	YAHOO.log(this.project+" == "+YAHOO.projectManager.getSelectedDBID());
	
	if(this.project == YAHOO.projectManager.getSelectedDBID()){
		YAHOO.projectManager.updateSelected(this);
	}
	
	return this.domElem;
}
