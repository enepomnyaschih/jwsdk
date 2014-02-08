function createTarget() {
	var self = {
		el: $('<div class="target" />'),
		onExplode: null
	};
	self.el.click(function() {
		self.el.remove();
		if (self.onExplode) {
			self.onExplode();
		}
	});
	return self;
}
