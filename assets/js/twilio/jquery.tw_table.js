(function($) {
	/*
		Function: $.tw_table
			This will render elements into dataTables that can be sorted and etc. This will fix onto of dataTables making the parameters more human and the ajax bug issue.
		
		Parameters:
			data - (null / string) null will pull data from table, while a URI will pull data from server
			options - (object) tw_table options.
				autowidth - (bool) auto-resize the columns to fit the data.				
				columns_hide - (array) will hide these columns indexes. ie. [2, 5]
				columns_hide_from_search - (array) will hide these columns from search. ie. [1, 3]
				columns_title - (array) titles of the columns. [null, Name, DOB, null]
                limit - (int / 10) number of records to show
				paginate - (bool) display pagination or not.
				pageination_type - (string) full_numbers or two_button.
				save_state - (bool) save in cookie the user's last table sort and other settings.
				sort - (bool) if this is false, sort_by doesn't work.
				sort_by - (array) array of column index and desc/asc option. ie. [[3,'desc'],[2,'asc']]				

                onServerData - (function) override datatable requests, data has to be an URL for this to work
                onRowRender - (function) allows you to modify each row
			override_options - (object) if preferred, you can use the dataTables options.
		
		Returns:
			dataTables object
	*/
	$.fn.tw_table = function(data, options, override_options) {
		if(!$.fn.dataTable) {
			return false;
		}
		
		var tw_table = {
			el: null,
			data: '',
			options: '',
			engine_obj: null
		}
		
		tw_table.el = this;
		tw_table.data = data;
		
		var default_options = {}
		options = $.extend({}, default_options, options);
		
		if(options) {
			if(options.paginate !== undefined) { options.bPaginate = options.paginate; delete options.paginate; }
			if(options.filter !== undefined) { options.bFilter = options.filter; delete options.filter; }
			if(options.sort !== undefined) { options.bSort = options.sort; delete options.sort; }
			if(options.autowidth !== undefined) { options.bAutoWidth = options.autowidth; delete options.autowidth; }
            if(options.limit != undefined) { options.iDisplayLength = options.limit; delete options.limit; }
			if(options.sort_by !== undefined) { options.aaSorting = options.sort_by; delete options.sort_by; }
			if(options.save_state !== undefined) { options.bStateSave = options.save_state; delete options.save_state; }
			if(options.pagination_type !== undefined) { options.sPaginationType = options.pagination_type; delete options.pagination_type; }

            if(options.onRowRender) { options.fnRowCallback = options.onRowRender; delete options.onoRowRender; }
			
			var x = 0;
			options.aoColumns = [];
			$.each(this.find('tr:first-child td', 0), function() {
				options.aoColumns.push({});
			});
			
			// Hide columns
			if(options.columns_hide) {
				$.each(options.columns_hide, function(i, col) {
					options.aoColumns[col].bVisible = false;
				});
				
				delete options.columns_hide;
			}
			
			// Hide columns from search
			if(options.columns_hide_from_search) {
				$.each(options.columns_hide_from_search, function(i, col) {
					options.aoColumns[col].bSearchable = false;
				});				
				
				delete options.columns_hide_from_search;
			}
			
			// Set column titles
			if(options.columns_title) {
				$.each(options.columns_title, function(i, title) {
					options.aoColumns[i].sTitle = title;
				});
			}
			
			// Server processing and TODO check if is valid url
			if(typeof data == 'string') {
				options.bProcessing = true;
				options.bServerSide = true;
				options.sAjaxSource = data;

                if(options.onServerData) { options.fnServerData = options.onServerData; delete options.onServerData; }
			}
		}
		
        if(override_options) {
		    options = $.extend({}, options, override_options);
		}

		tw_table.engine_obj = this.dataTable(options);
		return tw_table;
	}
})(jQuery);
