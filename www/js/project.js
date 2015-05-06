
function Project(conf) {
	
	this.objectName = conf.object_name;
	this.projectUrl = conf.project_url;
	this.activeModules = conf.modules;
	
	//var self = this;
	
	for (var mod in this.activeModules) {
		var modId = this.activeModules[mod];
		this['obj_'+modId] = new window[modId]({
			object_name: this.objectName, 
			project_url: this.projectUrl
		});
		this['obj_'+modId].init();
		console.log(modId + " initialized...");
	}
	
}
