(function ($) {
    $('.cms #NewsPerPage').entwine({
        loadPanel: function () {
            this.redrawTabs();
            this._super();
        },
        onmatch: function () {
            this.redrawTabs();
            this._super();
        },

        redrawTabs: function () {
            
            checkBackground();
            
            $('#Size').on('click', function () {
                checkBackground();
            });
            
            
            function checkBackground()
            {
                var size = $('#Size #Form_ItemEditForm_Size_chzn .chzn-drop ul li.result-selected').text();
                //console.log(size);
                $('#Form_ItemEditForm_NewsPerPage_chzn .chzn-drop ul li').each( function(e) {
                    $(this).show();
                });
                
                if(size === '1x1') {
                    $('#NewsPerPage #Form_ItemEditForm_NewsPerPage_chzn .chzn-drop ul li').each( function(e) {
                        if($(this).text() === '1') {
                            $('#NewsPerPage #Form_ItemEditForm_NewsPerPage_chzn a:first span:first').text('1');
                            $(this).addClass('result-selected');
                            $(this).show();
                        } else {
                            $(this).removeClass('result-selected');
                            $(this).hide();
                        }
                    }); 
                }
                
            }
        }
    });
    
    $('.cms #IncludedTags').entwine({
        loadPanel: function () {
            this.redrawTabs();
            this._super();
        },
        onmatch: function () {
            this.redrawTabs();
            this._super();
        },

        redrawTabs: function () {
            
            checkBackground();
            
            $('#Form_ItemEditForm_IncludedTags li input').on('click', function () {
                checkBackground();
            });
            
            $('#LocationBased input').on('click', function () {
                checkBackground();
            });
            
            $('#SchoolBased input').on('click', function () {
                checkBackground();
            });
            function checkBackground()
            {
                var numChecked = 0;
                $('#Form_ItemEditForm_IncludedTags li').each(function() {
                    var input = $(this).find('input');
                    if($(input).is(':checked')) {
                        numChecked++;
                    }
                });
                if(numChecked > 1) {
                    $('#Form_ItemEditForm_MainTag_chzn .chzn-results li').each(function() {
                        $(this).removeClass('result-selected');
                        $(this).hide();
                    });
                    
                    $('#Form_ItemEditForm_IncludedTags li').each(function() {
                        var inputState = $(this).find('input').is(':checked');
                        var inputText = $(this).find('label').text();
                        
                        if(inputState) {
                            var counter = 0; 
                            $('#Form_ItemEditForm_MainTag_chzn ul.chzn-results li').each(function() {
                                if($(this).text() == inputText) {
                                    $(this).show();
                                    if(counter < 1)
                                        $('#Form_ItemEditForm_MainTag_chzn a span').text(inputText);
                                    counter++;
                                }
                                
                            });
                           
                        } 
                        
                    });
                    $('#MainTag').toggle(true);
                } else {
                    
                    $('#MainTag').toggle(false);
                }
            }
        }
    });
    
    $('.cms #LocationBased').entwine({
        loadPanel: function () {
            this.redrawTabs();
            this._super();
        },
        onmatch: function () {
            this.redrawTabs();
            this._super();
        },

        redrawTabs: function () {
            
            checkBackground();
            
            $('#LocationBased input').on('click', function () {
                checkBackground();
            });
            
            function checkBackground()
            {
                $('#Form_ItemEditForm_IncludedTags li').each(function() {
                    var inputValue = $(this).find('input').val();
                    var input = $(this).find('input');
                    if(inputValue.indexOf("Campus") > 0) {
                        if($('#LocationBased input').is(':checked')) {
                            $(this).hide();
                            $(input).prop( "checked", false );
                        } else {
                            $(this).show();
                        }
                    }
                });
                
                $('#Form_ItemEditForm_ExcludedTags li').each(function() {
                    var inputValue = $(this).find('input').val();
                    var input = $(this).find('input');
                    if(inputValue.indexOf("Campus") > 0) {
                        if($('#LocationBased input').is(':checked')) {
                            $(this).hide();
                            $(input).prop( "checked", false );
                        } else {
                            $(this).show();
                        }
                    }
                });
                
            }
        }
    });
    
    $('.cms #SchoolBased').entwine({
        loadPanel: function () {
            this.redrawTabs();
            this._super();
        },
        onmatch: function () {
            this.redrawTabs();
            this._super();
        },

        redrawTabs: function () {
            
            checkBackground();
            
            $('#SchoolBased input').on('click', function () {
                checkBackground();
            });
            
            function checkBackground()
            {
                $('#Form_ItemEditForm_IncludedTags li').each(function() {
                    var inputValue = $(this).find('input').val();
                    var input = $(this).find('input');
                    if(inputValue.indexOf("Programme") > 0) {
                        if($('#SchoolBased input').is(':checked')) {
                            $(this).hide();
                            $(input).prop( "checked", false );
                        } else {
                            $(this).show();
                        }
                    } 
                });
                
                $('#Form_ItemEditForm_ExcludedTags li').each(function() {
                    var inputValue = $(this).find('input').val();
                    var input = $(this).find('input');
                    if(inputValue.indexOf("Programme") > 0) {
                        if($('#SchoolBased input').is(':checked')) {
                            $(this).hide();
                            $(input).prop( "checked", false );
                        } else {
                            $(this).show();
                        }
                    } 
                });
                
            }
        }
    });
})(jQuery);