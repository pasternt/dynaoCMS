$('#structure-tree li').prepend('<div class="dropzone"></div>');

    $('#structure-tree .handle, #structure-tree .dropzone').droppable({
        accept: '#structure-tree li',
        tolerance: 'pointer',
        drop: function(e, ui) {
            var li = $(this).parent();
            var child = !$(this).hasClass('dropzone');
            if (child && li.children('ul').length == 0) {
                li.append('<ul/>');
            }
            if (child) {
                li.addClass('sm2_liOpen').removeClass('sm2_liClosed').children('ul').append(ui.draggable);
            }
            else {
                li.before(ui.draggable);
            }
			$('#structure-tree li.sm2_liOpen').not(':has(li:not(.ui-draggable-dragging))').removeClass('sm2_liOpen');
            li.find('.handle,.dropzone').css({ backgroundColor: '', borderColor: '' });
			
			var object = $('#structure-tree');
					
			var returnArray = function()
			{
				var data,
					depth = 0,
					list = this;
					isIn = [];
					step = function(level)
					{
						var array = [ ],
							items = level.children('li');
						items.each(function()
						{
							var li = $(this),
								item = {};
								
							item.id = li.data('id');
							
							if($.inArray(item.id, isIn) >= 0) {
								return;	
							}							
									
							isIn.push(item.id);
							
							sub = li.children('ul');
								
							if (sub.length) {
								item.children = step(sub);
							}
							array.push(item);
							
						});
						return array;
					};
				data = JSON.stringify(step(object, depth));
				return data;
			};
			
			var getString = document.location.search.substr(1,document.location.search.length);
			
			$('#ajax-content').fadeOut(200);
			setTimeout(function() {
				$.post('index.php?'+getString, {array: returnArray() }, function(data) {
					$('#ajax-content').html(data).fadeIn(200);
				});
			}, 0);
			
        },
        over: function() {
            $(this).filter('.handle, .dropzone').css({ backgroundColor: '#ccc' });
        },
        out: function() {
            $(this).filter('.handle, .dropzone').css({ backgroundColor: '' });
        }
    });
	
    $('#structure-tree li').draggable({
        handle: ' > .handle',
        opacity: .8,
        addClasses: false,
        helper: 'clone',
        zIndex: 100,
    });