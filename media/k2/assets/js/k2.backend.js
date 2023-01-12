/**
 * @version    2.11 (rolling release)
 * @package    K2
 * @author     JoomlaWorks https://www.joomlaworks.net
 * @copyright  Copyright (c) 2009 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL: https://gnu.org/licenses/gpl.html
 */

var $K2 = jQuery.noConflict();

var K2JVersion;
var K2SitePath;
var selectsInstance;

$K2(document).ready(function() {

    // Set the selects instance to allow inheritance of jQuery chosen bindings
    if (typeof(K2JVersion) !== 'undefined' && K2JVersion === '30') {
        selectsInstance = jQuery;
    } else {
        selectsInstance = $K2;
    }

    // Generic function to get URL params passed in .js script include
    function getUrlParams(targetScript, varName) {
        var scripts = document.getElementsByTagName('script');
        var scriptCount = scripts.length;
        for (var a = 0; a < scriptCount; a++) {
            var scriptSrc = scripts[a].src;
            if (scriptSrc.indexOf(targetScript) >= 0) {
                varName = varName.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
                var re = new RegExp("[\\?&]" + varName + "=([^&#]*)");
                var parsedVariables = re.exec(scriptSrc);
                if (parsedVariables !== null) {
                    return parsedVariables[1];
                }
            }
        }
    }

    // Set the site root path
    K2SitePath = getUrlParams('k2.backend.js', 'sitepath');

    // --- Common functions ---

    // Minimal Scrollspy
    $K2(".k2ScrollSpyMenu").each(function(index) {
        // Cache selectors
        var lastId,
            menuItems = $K2(this).find("a"),
            firstMenuItem = menuItems[0],
            // Anchors corresponding to menu items
            scrollItems = menuItems.map(function() {
                var item = $K2($K2(this).attr("href"));
                if (item.length) return item;
            });

        // Bind click handler to menu items so we can get a fancy scroll animation
        menuItems.click(function(e) {
            var href = $K2(this).attr("href"),
                offsetTop = (href === "#") ? 0 : $K2(href).offset().top - 60;
            $K2('html, body').stop().animate({
                scrollTop: offsetTop - 88
            }, 300);
            e.preventDefault();
        });

        // Bind to scroll
        $K2(window).scroll(function() {
            // Get container scroll position
            var fromTop = $K2(this).scrollTop() + 100;

            // Get id of current scroll item
            var cur = scrollItems.map(function() {
                if ($K2(this).offset().top < fromTop) return this;
            });
            // Get the id of the current element
            cur = cur[cur.length - 1];
            var id = cur && cur.length ? cur[0].id : "";

            if (lastId !== id) {
                lastId = id;
                // Set/remove active class (needs fixing)
                //menuItems.parent().removeClass("active").end().filter('a[href="#' + id + '"]').parent().addClass("active");
            }
        });
    });

    // Form filters reset
    $K2('#k2ResetButton').click(function(event) {
        event.preventDefault();
        $K2('.k2AdminTableFilters input').val('');
        $K2('.k2AdminTableFilters option').removeAttr('selected');
        this.form.submit();
    });
    selectsInstance('.k2AdminTableFilters select').change(function() {
        this.form.submit();
    });

    // View specific functions
    if ($K2('#k2AdminContainer').length > 0) {
        var K2IsAdmin = true;
        var view = $K2('#k2AdminContainer input[name=view]').val();
    } else {
        var K2IsAdmin = false;
        var view = $K2('#k2ModalContainer input[name=view]').val();
    }

    // Report user
    $K2('.k2ReportUserButton').click(function(event) {
        event.preventDefault();
        if (view == 'comments') {
            var alert = K2Language[2];
        } else {
            var alert = K2Language[0];
        }
        if (confirm(alert)) {
            window.location.href = $K2(this).attr('href');
        }
    });

    switch (view) {

        case 'comments':
            var flag = false;
            if (K2IsAdmin) {
                K2SitePath += 'administrator/';
            }
            $K2('.editComment').click(function(event) {
                event.preventDefault();
                if (flag) {
                    alert(K2Language[0]);
                    return;
                }
                flag = true;
                var commentID = $K2(this).attr('rel');
                var target = $K2('#k2Comment' + commentID + ' .commentText');
                var value = target.text();
                $K2('#k2Comment' + commentID + ' input').val(value);
                target.empty();
                var textarea = $K2('<textarea/>', {
                    name: 'comment',
                    rows: '5',
                    cols: '40'
                });
                textarea.html(value).appendTo(target);
                textarea.focus();
                $K2('#k2Comment' + commentID + ' .commentToolbar .k2CommentControls').css('display', 'inline');
                $K2(this).css('display', 'none');
            });
            $K2('.saveComment').click(function(event) {
                event.preventDefault();
                flag = false;
                var commentID = $K2(this).attr('rel');
                var target = $K2('#k2Comment' + commentID + ' .commentText');
                var value = $K2('#k2Comment' + commentID + ' .commentText textarea').val();
                $K2('#task').val('saveComment');
                $K2('#commentID').val(commentID);
                $K2('#commentText').val(value);
                var log = $K2('#k2Comment' + commentID + ' .k2CommentsLog');
                log.addClass('k2CommentsLoader');

                $K2.ajax({
                    url: K2SitePath + 'index.php',
                    type: 'post',
                    dataType: 'json',
                    data: $K2('#adminForm').serialize(),
                    success: function(result) {
                        target.html(result.comment);
                        $K2('#k2Comment' + commentID + ' input').val(result.comment);
                        $K2('#task').val('');
                        log.removeClass('k2CommentsLoader').html(result.message).delay(2000).fadeOut();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        log.removeClass('k2CommentsLoader').html(textStatus + ': ' + errorThrown).delay(2000).fadeOut();
                    }
                });
                $K2('#k2Comment' + commentID + ' .commentToolbar .k2CommentControls').css('display', 'none');
                $K2('#k2Comment' + commentID + ' .commentToolbar a.editComment').css('display', 'inline');
            });
            $K2('.closeComment').click(function(event) {
                event.preventDefault();
                flag = false;
                var commentID = $K2(this).attr('rel');
                var target = $K2('#k2Comment' + commentID + ' .commentText');
                var value = $K2('#k2Comment' + commentID + ' input').val();
                target.html(value);
                $K2('#k2Comment' + commentID + ' .commentToolbar .k2CommentControls').css('display', 'none');
                $K2('#k2Comment' + commentID + ' .commentToolbar a.editComment').css('display', 'inline');
            });
            if ($K2('input[name=isSite]').val() == 1) {
                // Close comments moderation modal/window
                $K2('#toolbar-cancel a').click(function(event) {
                    event.preventDefault();
                    // Close modal
                    if (typeof(parent.$K2.magnificPopup) !== 'undefined') {
                        parent.window.location.reload();
                        parent.$K2.magnificPopup.close();
                    }
                    // Close window/tab
                    if (top == self) {
                        window.close();
                    }
                });
                // Pagination
                $K2('.k2CommentsPagination a').each(function() {
                    var pageURL = $K2(this).attr('href');
                    if (pageURL.indexOf('limitstart=') < 0) {
                        if (pageURL.indexOf('?start=') > 0) {
                            $K2(this).attr('href', pageURL.replace('?start=', '?limitstart='));
                        } else if (pageURL.indexOf('&start=') > 0) {
                            $K2(this).attr('href', pageURL.replace('&start=', '&limitstart='));
                        } else {
                            var currentPageUrl = window.location.href;
                            if (currentPageUrl.indexOf('?') > 0) {
                                var ls = '&limitstart=0';
                            } else {
                                var ls = '?limitstart=0';
                            }
                            $K2(this).attr('href', pageURL + ls);
                        }
                    }
                });
            }
            break;

        case 'extrafield':
            if ($K2('#groups').val() > 0) {
                $K2('#groupContainer').fadeOut(0);
            }
            selectsInstance('#groups').change(function() {
                var selectedValue = selectsInstance(this).val();
                if (selectedValue == 0) {
                    $K2('#group').val('');
                    $K2('#isNew').val('1');
                    $K2('#groupContainer').fadeIn('slow');
                } else {
                    $K2('#groupContainer').fadeOut('slow', function() {
                        $K2('#group').val(selectedValue);
                        $K2('#isNew').val('0');
                    });
                }
            });
            if ($K2('input[name=id]').val()) {
                newField = 0;
            } else {
                newField = 1;
            }
            if (!newField) {
                var values = $K2.parseJSON($K2('#value').val());
            } else {
                var values = new Array();
                values[0] = " ";
            }
            renderExtraFields($K2('#type').val(), values, newField);
            selectsInstance('#type').change(function() {
                var selectedType = selectsInstance(this).val();
                $K2('#k2ExtraFieldsShowNullFlag').fadeOut('slow');
                $K2('#k2ExtraFieldsDisplayInFrontEndFlag').fadeOut('slow');
                $K2('#k2app-ef-header-flag').fadeOut('slow');
                $K2('#k2app-ef-type-data').fadeOut('slow', function() {
                    $K2('#k2app-ef-type-data').empty();
                    renderExtraFields(selectedType, values, newField);
                    $K2('#k2app-ef-type-data').fadeIn('slow');
                    if (selectedType === 'select' || selectedType === 'multipleSelect') {
                        $K2('#k2ExtraFieldsShowNullFlag').fadeIn('slow');
                    }
                    if (selectedType !== 'header') {
                        $K2('#k2app-ef-header-flag').fadeIn('slow');
                    }
                    if (selectedType === 'header') {
                        $K2('#k2ExtraFieldsDisplayInFrontEndFlag').fadeIn('slow');
                    }
                });
            });
            extraFieldsImage();
            break;

        case 'usergroup':
            var value = $K2('input[name=categories]:checked').val();
            if (value == 'all') {
                selectsInstance('#paramscategories').attr('disabled', 'disabled');
                selectsInstance('#paramscategories option').each(function() {
                    selectsInstance(this).attr('disabled', 'disabled');
                    selectsInstance(this).attr('selected', 'selected');
                });
            } else if (value == 'none') {
                selectsInstance('#paramscategories').attr('disabled', 'disabled');
                selectsInstance('#paramscategories option').each(function() {
                    selectsInstance(this).attr('disabled', 'disabled');
                    selectsInstance(this).removeAttr('selected');
                });
            } else {
                selectsInstance('#paramscategories').removeAttr('disabled');
                selectsInstance('#paramscategories option').each(function() {
                    selectsInstance(this).removeAttr('disabled');
                });
            }
            selectsInstance('#categories-all').click(function() {
                selectsInstance('#paramscategories').attr('disabled', 'disabled');
                selectsInstance('#paramscategories option').each(function() {
                    selectsInstance(this).attr('disabled', 'disabled');
                    selectsInstance(this).attr('selected', 'selected');
                });
                selectsInstance("#paramscategories").trigger("liszt:updated");
            });
            selectsInstance('#categories-none').click(function() {
                selectsInstance('#paramscategories').attr('disabled', 'disabled');
                selectsInstance('#paramscategories option').each(function() {
                    selectsInstance(this).attr('disabled', 'disabled');
                    selectsInstance(this).removeAttr('selected');
                });
                selectsInstance("#paramscategories").trigger("liszt:updated");
            });
            selectsInstance('#categories-select').click(function() {
                selectsInstance('#paramscategories').removeAttr('disabled');
                selectsInstance('#paramscategories option').each(function() {
                    selectsInstance(this).removeAttr('disabled');
                });
                selectsInstance("#paramscategories").trigger("liszt:updated");
            });
            break;

        case 'categories':
            $K2('#K2BatchButton').click(function(event) {
                event.preventDefault();
                var checked = $K2('input[name="cid[]"]:checked').length;
                $K2('#k2BatchOperationsCounter').text(checked);
                if (checked > 0) {
                    $K2('#k2BatchOperations').addClass('k2ui-modal-open');
                    $K2('#batchCategory option').removeAttr('disabled');
                    $K2('input[name="cid[]"]:checked').each(function() {
                        $K2('#batchCategory option[value="' + $K2(this).val() + '"]').attr('disabled', 'disabled');
                        $K2('#batchCategory').trigger('liszt:updated');
                    });
                } else {
                    alert(K2SelectItemsError);
                }
            });
            $K2('#K2MoveButton').click(function(event) {
                event.preventDefault();
                var checked = $K2('input[name="cid[]"]:checked').length;
                $K2('#k2MoveOperationsCounter').text(checked);
                if (checked > 0) {
                    $K2('#k2MoveOperations').addClass('k2ui-modal-open');
                    $K2('#moveCategories option').removeAttr('disabled');
                    $K2('input[name="cid[]"]:checked').each(function() {
                        $K2('#moveCategories option[value="' + $K2(this).val() + '"]').attr('disabled', 'disabled');
                        $K2('#moveCategories').trigger('liszt:updated');
                    });
                } else {
                    alert(K2SelectItemsError);
                }
            });
            break;

        case 'category':
            $K2('.k2Tabs').tabs();
            $K2('#k2ImageBrowseServer').click(function(event) {
                event.preventDefault();
                SqueezeBox.initialize();
                SqueezeBox.fromElement(this, {
                    handler: 'iframe',
                    url: K2BasePath + 'index.php?option=com_k2&view=media&type=image&tmpl=component&fieldID=existingImageValue',
                    size: {
                        x: (window.innerWidth) * 0.9,
                        y: (window.innerHeight) * 0.9
                    }
                });
            });
            break;

        case 'items':
            $K2('#K2BatchButton').click(function(event) {
                event.preventDefault();
                var checked = $K2('input[name="cid[]"]:checked').length;
                $K2('#k2BatchOperationsCounter').text(checked);
                if (checked > 0) {
                    $K2('#k2BatchOperations').addClass('k2ui-modal-open');
                } else {
                    alert(K2SelectItemsError);
                }
            });
            $K2('#K2MoveButton').click(function(event) {
                event.preventDefault();
                var checked = $K2('input[name="cid[]"]:checked').length;
                $K2('#k2MoveOperationsCounter').text(checked);
                if (checked > 0) {
                    $K2('#k2MoveOperations').addClass('k2ui-modal-open');
                } else {
                    alert(K2SelectItemsError);
                }
            });
            break;

        case 'item':
            $K2('.k2Tabs').tabs();
            if (typeof(K2ActiveMediaTab) === 'undefined') {
                $K2('#k2MediaTabs').tabs();
            } else {
                $K2('#k2MediaTabs').tabs({
                    selected: K2ActiveMediaTab
                });
            }
            $K2('#k2ToggleSidebar').click(function(event) {
                event.preventDefault();
                $K2('#adminFormK2Sidebar').toggle();
            });
            $K2('#catid option[disabled]').css('color', '#808080');
            setTimeout(function() {
                initExtraFieldsEditor();
            }, 1000);
            $K2('.deleteAttachmentButton').click(function(event) {
                event.preventDefault();
                if (confirm(K2Language[3])) {
                    var element = $K2(this).parent().parent();
                    var url = $K2(this).attr('href');
                    $K2.ajax({
                        url: url,
                        type: 'get',
                        success: function() {
                            $K2(element).fadeOut('fast', function() {
                                $K2(element).remove();
                            });
                        }
                    });
                }
            });
            $K2('#resetHitsButton').click(function(event) {
                event.preventDefault();
                Joomla.submitbutton('resetHits');
            });
            $K2('#resetRatingButton').click(function(event) {
                event.preventDefault();
                Joomla.submitbutton('resetRating');
            });
            $K2('#addAttachmentButton').click(function(event) {
                event.preventDefault();
                addAttachment();
            });
            $K2('#newTagButton').click(function() {
                var log = $K2('#tagsLog');
                log.empty().addClass('tagsLoading');
                var tag = $K2('#tag').val();
                var url = 'index.php?option=com_k2&view=item&task=tag&tag=' + tag;
                $K2.ajax({
                    url: url,
                    type: 'get',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            var option = $K2('<option/>', {
                                value: response.id
                            }).html(response.name).appendTo($K2('#tags'));
                        }
                        log.html(response.msg);
                        log.removeClass('tagsLoading');
                    }
                });
            });
            $K2('#addTagButton').click(function() {
                $K2('#tags option:selected').each(function() {
                    $K2(this).appendTo($K2('#selectedTags'));
                });
            });
            $K2('#removeTagButton').click(function() {
                $K2('#selectedTags option:selected').each(function(el) {
                    $K2(this).appendTo($K2('#tags'));
                });
            });
            $K2('#k2AdminContainer').on('click', 'input[type="radio"]', function(e) {
                $K2(this).parents('.controls').children('label.radio').each(function() {
                    $K2(this).removeClass('isChecked');
                });
                $K2(this).parent().addClass('isChecked');
            });
            selectsInstance('#catid').change(function() {
                if (selectsInstance(this).find('option:selected').attr('disabled')) {
                    alert(K2Language[4]);
                    selectsInstance(this).val('0');
                    return;
                }
                var selectedValue = $K2(this).val();
                var url = K2BasePath + 'index.php?option=com_k2&view=item&task=extraFields&context=ajax&cid=' + selectedValue + '&id=' + $K2('input[name=id]').val();
                $K2('#extraFieldsContainer').fadeOut('slow', function() {
                    $K2.ajax({
                        url: url,
                        type: 'get',
                        success: function(response) {
                            $K2('#extraFieldsContainer').html(response);
                            initExtraFieldsEditor();

                            // Load Flatpickr
                            $K2('.k2Calendar').each(function() {
                                $K2(this).flatpickr({
                                    allowInput: true
                                });
                                inputFieldID = $K2(this).attr('id');
                            });

                            $K2('#extraFieldsContainer').fadeIn('slow');
                        }
                    });
                });
            });
            $K2('#k2ImageBrowseServer').click(function(event) {
                event.preventDefault();
                SqueezeBox.initialize();
                SqueezeBox.fromElement(this, {
                    handler: 'iframe',
                    url: K2BasePath + 'index.php?option=com_k2&view=media&type=image&tmpl=component&fieldID=existingImageValue',
                    size: {
                        x: (window.innerWidth) * 0.9,
                        y: (window.innerHeight) * 0.9
                    }
                });
            });
            $K2('#k2MediaBrowseServer').click(function(event) {
                event.preventDefault();
                SqueezeBox.initialize();
                SqueezeBox.fromElement(this, {
                    handler: 'iframe',
                    url: K2BasePath + 'index.php?option=com_k2&view=media&type=video&tmpl=component&fieldID=remoteVideo',
                    size: {
                        x: (window.innerWidth) * 0.9,
                        y: (window.innerHeight) * 0.9
                    }
                });
            });
            $K2('#itemAttachments').on('click', '.k2AttachmentBrowseServer', function(event) {
                event.preventDefault();
                var k2ActiveAttachmentField = $K2(this).prev();
                k2ActiveAttachmentField.attr('id', 'k2ActiveAttachment');
                SqueezeBox.initialize();
                SqueezeBox.fromElement(this, {
                    handler: 'iframe',
                    url: K2BasePath + 'index.php?option=com_k2&view=media&type=attachment&tmpl=component&fieldID=k2ActiveAttachment',
                    size: {
                        x: (window.innerWidth) * 0.9,
                        y: (window.innerHeight) * 0.9
                    },
                    onClose: function() {
                        k2ActiveAttachmentField.removeAttr('id');
                    }
                });
            });
            $K2('.tagRemove').click(function(event) {
                event.preventDefault();
                $K2(this).parent().remove();
            });
            $K2('ul.tags').click(function() {
                $K2('#search-field').focus();
            });
            $K2('#search-field').keypress(function(event) {
                if (event.which == '13') {
                    if ($K2(this).val() != '') {
                        $K2('<li class="tagAdded">' + $K2(this).val() + '<span class="tagRemove" onclick="$K2(this).parent().remove();">&times;</span><input type="hidden" value="' + $K2(this).val() + '" name="tags[]"></li>').insertBefore('.tags .tagAdd');
                        $K2(this).val('');
                        event.preventDefault();
                    }
                }
            });
            var tagsUrl = K2SitePath;
            if (K2IsAdmin) {
                tagsUrl += 'administrator/';
            }
            tagsUrl += 'index.php?option=com_k2&view=item&task=tags';
            $K2('#search-field').autocomplete({
                source: function(request, response) {
                    $K2.ajax({
                        type: 'post',
                        url: tagsUrl,
                        data: 'q=' + request.term,
                        dataType: 'json',
                        success: function(data) {
                            $K2('#search-field').removeClass('tagsLoading');
                            response($K2.map(data, function(item) {
                                return item;
                            }));
                        }
                    });
                },
                minLength: 3,
                select: function(event, ui) {
                    $K2('<li class="tagAdded">' + ui.item.label + '<span class="tagRemove" onclick="$K2(this).parent().remove();">&times;</span><input type="hidden" value="' + ui.item.value + '" name="tags[]"></li>').insertBefore('.tags .tagAdd');
                    this.value = '';
                    return false;
                },
                search: function(event, ui) {
                    $K2('#search-field').addClass('tagsLoading');
                }
            });

            if ($K2('input[name=isSite]').val() == 1) {
                // Close item form modal/window
                $K2('#toolbar-cancel a').click(function(event) {
                    event.preventDefault();
                    var k2ItemId = $K2('input[name=id]').val();
                    var sigProFolder = $K2('input[name=sigProFolder]').val();
                    $K2.ajax({
                        type: 'get',
                        cache: false,
                        url: K2SitePath + 'index.php?option=com_k2&view=item&task=checkin&cid=' + k2ItemId + '&lang=' + $K2('input[name=lang]').val() + '&sigProFolder=' + sigProFolder,
                        success: function() {
                            // Close modal
                            if (typeof(parent.$K2.magnificPopup) !== 'undefined') {
                                parent.window.location.reload();
                                parent.$K2.magnificPopup.close();
                            }
                            // Close window/tab
                            if (top == self) {
                                window.close();
                            }
                        }
                    });
                });
            }
            extraFieldsImage();
            break;
    }

    // Add the correct CSS classes for the checked labels
    $K2('label.radio').has('input:checked').addClass('isChecked');

    // Toggle clickable Labels
    $K2('label.radio').has('input').click(function(e) {

        $K2(this).parent().children().removeClass('isChecked');

        if (!$K2(this).hasClass('isChecked')) {
            $K2(this).addClass('isChecked');
        }
    });
});



/*
 * JS encapsulated behind the "jQuery" object - added in K2 v2.8.0+
 */
(function($) {
    // --- Helper Functions ---
    // Character count (usually placed on a textarea element)
    $.fn.k2CharCount = function(el, max) {
        var container = $(el).parent();
        container.append('<span class="k2CharCounter">&nbsp;</span>');
        var counter = $(container).find('.k2CharCounter')[0];
        $(el).on('focus keydown keyup', function() {
            var count = max - $(el).val().length;
            if (count < 0) {
                $(counter).attr('class', 'k2CharCounter k2CharsExceeded');
            } else {
                $(counter).attr('class', 'k2CharCounter');
            }
            $(counter).html(count);
        });
    }

    // Pseudo-alert
    $.fn.k2Alert = function(msg, duration) {
        if ($('#k2AlertContainer').length) {
            $('#k2AlertContainer').remove();
        }
        $('body').append('<div id="k2AlertContainer"><div id="k2AlertMessage"><a href="#" id="k2AlertClose">&times;</a><span>' + msg + '</span></div></div>');
        $('#k2AlertClose').on('click', function(e) {
            e.preventDefault();
            $('#k2AlertContainer').remove();
        });
        $('#k2AlertContainer').delay(duration).fadeOut('fast', function() {
            $(this).remove();
        });
    }

    // -- Load everything up ---
    $(document).ready(function() {
        // Standard Toggler
        $('#jToggler, #k2standard, #k2TogglerStandard').click(function() {
            var checkBoxes = $('input[id^=cb]');
            checkBoxes.prop('checked', !checkBoxes.prop('checked'));
            $(this).prop('checked', checkBoxes.is(':checked'));
            $('input[name=boxchecked]').val($('input[id^=cb]:checked').length);
        });

        // True Toggler
        $('#k2TogglerTrue').click(function() {
            var checkBoxes = $('input[id^=cb]');
            checkBoxes.trigger('click');
            $('input[name=boxchecked]').val($('input[id^=cb]:checked').length);
        });

        // Submit form
        $('#k2SubmitButton').click(function() {
            this.form.submit();
        });

        // Hide system messages after 3 seconds in frontend editing
        if ($('#k2ModalContainer').length && $('#system-message-container').length) {
            $('#system-message-container').delay(3000).fadeOut('fast', function() {
                $(this).remove();
            });
        }

        // Sortables (jQuery UI)
        if ($('.k2SortableListContainer').length) {
            $('.k2SortableListContainer').sortable();
            $('.k2SortableListContainer .k2EntryRemove').on('click', function(e) {
                e.preventDefault();
                $(this).parent().remove();
            });
        }

        // Single Items
        if ($('.k2SingleSelect').length) {
            $('.k2SingleSelect .k2EntryRemove').on('click', function(e) {
                e.preventDefault();
                $(this).parent().remove();
            });
        }

        // Flatpickr
        if ($('input[data-k2-datetimepicker]').length) {
            $('input[data-k2-datetimepicker]').each(function() {
                var options = $(this).data('k2Datetimepicker');
                if (options) {
                    $(this).flatpickr(options);
                } else {
                    $(this).flatpickr({
                        enableTime: true,
                        enableSeconds: true,
                        allowInput: true
                    });
                }
            });
        }

        // Assist parameter styling
        if ($('.jwHeaderContainer').length) {
            $('.jwHeaderContainer').each(function() {
                $(this).parents('.control-group').addClass('control-group-header');
            });
        }

        // Magnific Popup
        if (typeof($.magnificPopup) !== 'undefined') {
            $('[data-k2-modal="image"]').magnificPopup({
                type: 'image',
                image: {
                    titleSrc: function() {
                        return '';
                    }
                }
            });
            $('[data-k2-modal="edit"]').magnificPopup({
                type: 'iframe',
                modal: true
            });
            $('[data-k2-modal="iframe"]').magnificPopup({
                type: 'iframe'
            });
            //$('[data-k2-modal="iframe"]').magnificPopup({type:'iframe', iframe: {markup: '<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe></div>'}});
            $('[data-k2-modal="singleSelect"]').magnificPopup({
                type: 'iframe',
                modal: true,
                closeOnContentClick: true
            });
        }

        if ($('#k2CloseMfp').length) {
            $('#k2CloseMfp').on('click', function(e) {
                e.preventDefault();
                window.parent.k2ModalClose();
            });
        }

        // Character count
        if ($('[data-k2-chars]').length) {
            $('[data-k2-chars]').each(function() {
                var count = $(this).data('k2Chars');
                if (count) {
                    $(this).k2CharCount($(this), count);
                }
            });
        }

        // Hide system messages (if any)
        var msgContainers = ['#system-message-container', '#system-message'];
        $(msgContainers).each(function(i) {
            if ($(msgContainers[i])) {
                $(msgContainers[i]).delay(3000).fadeOut(500, function() {
                    $(this).html('');
                });
            }
        });

        // Responsive
        if ($('#k2ui-menu-control').css('display') == 'block') {
            $('#k2ui-menu-control').on('click', function() {
                $('#k2Sidebar ul').toggle();
            });
        }
    });

    function doOnReloadOrResize() {
        // Disable Chosen for Flatpickr instances
        if (typeof $.fn.chosen !== 'undefined') {
            if ($('.flatpickr-calendar').length) {
                $('.flatpickr-calendar select').chosen('destroy');
            }
        }

        // Responsive
        /*
        if ($('#k2ui-menu-control').css('display') == 'block') {
            if ($('.isJ30 div#toolbar').length) {
                var toolbarHeight = $('.isJ30 div#toolbar').height();
                $('#k2Sidebar #k2ui-menu-control').css('top', (-1 * toolbarHeight / 2) - 40);
            }
        }
        */

        // Adjust list top offset based on filters height
        if ($('.k2AdminTableFilters').length) {
            var filterHeight = $('.k2AdminTableFilters').first().height();
            if (filterHeight > 60 && $('#k2AdminContainer').width() > 1024 && $('#k2ui-menu-control').css('display') != 'block') {
                $('#k2ContentView > form .k2AdminTableData').css('padding-top', filterHeight);
            }
        }
    }

    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            doOnReloadOrResize();
        }, 250);
    });

    $(window).on('load', function() {
        doOnReloadOrResize();
    });
})(jQuery);



/*
 * Utility functions
 */

// Extra fields validation
function validateExtraFields() {
    $K2('.k2Required').removeClass('k2Invalid');
    $K2('#tabExtraFields a').removeClass('k2Invalid');
    var response = new Object();
    var efResults = [];
    response.isValid = true;
    response.errorFields = new Array();
    $K2('.k2Required').each(function() {
        var id = $K2(this).attr('id');
        var value;
        if ($K2(this).hasClass('k2ExtraFieldEditor')) {
            if (typeof tinymce != 'undefined') {
                var value = tinyMCE.get(id).getContent();
            }
        } else {
            var value = $K2(this).val();
        }
        if (($K2.trim(value) === '') || ($K2(this).hasClass('k2ExtraFieldEditor') && $K2.trim(value) === '<p></p>')) {
            $K2(this).addClass('k2Invalid');
            response.isValid = false;
            var label = $K2('label[for="' + id + '"]').text();
            response.errorFields.push(label);
        }
    });
    $K2.each(response.errorFields, function(key, value) {
        efResults.push('<li>' + value + '</li>');
    });
    if (response.isValid === false) {
        $K2('#k2ExtraFieldsMissing').html(efResults);
        $K2('#k2ExtraFieldsValidationResults').css('display', 'block');
        $K2('#tabExtraFields a').addClass('k2Invalid');
    }
    return response.isValid;
}

// Extra Fields - image field
function extraFieldsImage() {
    $K2('body').on('click', '.k2app-ef-image-button', function(event) {
        event.preventDefault();
        var href = $K2(this).attr('href');
        SqueezeBox.initialize();
        SqueezeBox.fromElement(this, {
            handler: 'iframe',
            url: K2BasePath + href,
            size: {
                x: (window.innerWidth) * 0.9,
                y: (window.innerHeight) * 0.9
            }
        });
    });
}

// If we are in Joomla 1.5 define the functions for validation
if (typeof(Joomla) === 'undefined') {
    var Joomla = {};
    Joomla.submitbutton = function(pressbutton) {
        submitform(pressbutton);
    };

    function submitbutton(pressbutton) {
        Joomla.submitbutton(pressbutton);
    }
}

// Extra fields
function renderExtraFields(fieldType, fieldValues, isNewField) {
    var target = $K2('#k2app-ef-type-data');
    var currentType = $K2('#type').val();

    switch (fieldType) {

        case 'textfield':
            var text = '';
            if (!isNewField && currentType == fieldType) {
                text = (fieldValues[0].value ? fieldValues[0].value : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <input name="option_value[]" type="text" value="' + text + '" /><span class="k2ui-ef-notice">(' + K2Language[1] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;

        case 'textarea':
            var textarea = '',
                rows = '',
                cols = '',
                editorValue = '',
                editorChecked = '';
            if (!isNewField && currentType == fieldType) {
                textarea = fieldValues[0].value;
                rows = (fieldValues[0].rows ? fieldValues[0].rows : '');
                cols = (fieldValues[0].cols ? fieldValues[0].cols : '');
                editorValue = (fieldValues[0].editor ? fieldValues[0].editor : '');
                editorChecked = (editorValue ? ' checked' : '');
            }
            var html = '\
                <textarea name="option_value[]" cols="40" rows="10">' + textarea + '</textarea>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-label">' + K2Language[16] + '</span>\
                    <input name="option_cols[]" type="text" value="' + cols + '" />\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-label">' + K2Language[17] + '</span>\
                    <input name="option_rows[]" type="text" value="' + rows + '" />\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-label">' + K2Language[3] + '</span>\
                    <input name="option_editor[]" type="checkbox" value="1"' + editorChecked + ' />\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-notice">(' + K2Language[4] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;

        case 'labels':
            var label = '';
            if (!isNewField && currentType == fieldType) {
                label = (fieldValues[0].value ? fieldValues[0].value : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <input name="option_value[]" type="text" value="' + label + '" />\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-notice">' + K2Language[2] + ' (' + K2Language[1] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;

        case 'select':
        case 'multipleSelect':
        case 'radio':
            var label = '';
            if (!isNewField && currentType == fieldType) {
                label = (fieldValues[0].value ? fieldValues[0].value : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <a id="k2app-ef-add-option" class="k2Button" href="#add">' + K2Language[5] + '</a>\
                </div>\
                <div id="select_radio_fields">\
            ';
            if (isNewField || currentType != fieldType) {
                html += '\
                    <div class="k2ui-ef-row">\
                        <input name="option_name[]" type="text" value="" />\
                        <input name="option_value[]" type="hidden" value="" />\
                        <a class="k2app-ef-remove-option k2Button" href="#remove">' + K2Language[0] + '</a>\
                    </div>\
                ';
            } else {
                $K2.each(fieldValues, function(i, e) {
                    name = (e.name ? e.name : '');
                    value = (e.value ? e.value : '');
                    html += '\
                        <div class="k2ui-ef-row">\
                            <input name="option_name[]" type="text" value="' + name + '" />\
                            <input name="option_value[]" type="hidden" value="' + value + '" />\
                            <a class="k2app-ef-remove-option k2Button" href="#remove">' + K2Language[0] + '</a>\
                        </div>\
                    ';
                });
            }
            html += '\
                </div>\
            ';
            $K2(html).appendTo(target);

            $K2('#select_radio_fields').on('click', '.k2app-ef-remove-option', function(e) {
                e.preventDefault();
                if ($K2('.k2app-ef-remove-option').length > 1) {
                    $K2('.k2app-ef-remove-option').each(function() {
                        if ($K2(this).hasClass('k2ButtonDisabled')) {
                            $K2(this).removeClass('k2ButtonDisabled');
                        }
                    });
                    $K2(this).parent().remove();
                } else {
                    $K2('.k2app-ef-remove-option').first().parent().find('input[type="text"]').attr('value', '');
                    $K2('.k2app-ef-remove-option').first().parent().find('input[type="hidden"]').attr('value', '');
                    $K2('.k2app-ef-remove-option').first().addClass('k2ButtonDisabled');
                }
            });

            $K2('#k2app-ef-add-option').on('click', function(e) {
                e.preventDefault();
                var copy = $K2('#select_radio_fields .k2ui-ef-row').first().clone();
                $K2(copy).find('input[type="text"]').attr('value', '');
                $K2(copy).find('input[type="hidden"]').attr('value', '');
                $K2(copy).appendTo($K2('#select_radio_fields'));
                if ($K2('.k2app-ef-remove-option').length > 1) {
                    $K2('.k2app-ef-remove-option').each(function() {
                        if ($K2(this).hasClass('k2ButtonDisabled')) {
                            $K2(this).removeClass('k2ButtonDisabled');
                        }
                    });
                } else {
                    $K2('.k2app-ef-remove-option').first().parent().find('input[type="text"]').attr('value', '');
                    $K2('.k2app-ef-remove-option').first().parent().find('input[type="hidden"]').attr('value', '');
                    $K2('.k2app-ef-remove-option').first().addClass('k2ButtonDisabled');
                }
            });

            break;

        case 'link':
            var linkText = '',
                linkUrl = '',
                linkTarget = '';
            if (!isNewField && currentType == fieldType) {
                linkText = (fieldValues[0].name ? fieldValues[0].name : '');
                linkUrl = (fieldValues[0].value ? fieldValues[0].value : '');
                linkTarget = (fieldValues[0].target ? fieldValues[0].target : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-label">' + K2Language[6] + '</span>\
                    <input name="option_name[]" type="text" value="' + linkText + '" />\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-label">' + K2Language[7] + '</span>\
                    <input name="option_value[]" type="text" value="' + linkUrl + '" />\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-label">' + K2Language[8] + '</span>\
                    <select name="option_target[]">\
                        <option value="same"' + (linkTarget == 'same' ? ' selected="selected"' : '') + '>' + K2Language[9] + '</option>\
                        <option value="new"' + (linkTarget == 'new' ? ' selected="selected"' : '') + '>' + K2Language[10] + '</option>\
                        <option value="popup"' + (linkTarget == 'popup' ? ' selected="selected"' : '') + '>' + K2Language[11] + '</option>\
                        <option value="lightbox"' + (linkTarget == 'lightbox' ? ' selected="selected"' : '') + '>' + K2Language[12] + '</option>\
                    </select>\
                </div>\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-notice">(' + K2Language[4] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;

        case 'csv':
            var input = $K2('<input/>', {
                name: 'csv_file',
                type: 'file'
            }).appendTo(target);
            var inputValue = $K2('<input/>', {
                name: 'option_value[]',
                type: 'hidden'
            }).appendTo(target);
            if (!isNewField && currentType == fieldType && fieldValues[0].value) {
                var csvAsJson = fieldValues[0].value;

                inputValue.val(JSON.stringify(csvAsJson));

                var table = $K2('<table/>', {
                    'class': 'k2ui-ef-csv'
                }).appendTo(target);

                $K2(csvAsJson).each(function(index, row) {
                    var tr = $K2('<tr/>').appendTo(table);
                    row.each(function(c, cell) {
                        if (index > 0) {
                            var td = $K2('<td/>').html(c).appendTo(tr);
                        } else {
                            var th = $K2('<th/>').html(c).appendTo(tr);
                        }
                    });
                });

                var html = '\
                    <hr />\
                    <div class="k2ui-ef-row">\
                        <input name="K2ResetCSV" type="checkbox" /><label>' + K2Language[13] + '</label> <span class="k2ui-ef-notice">(' + K2Language[1] + ')</span>\
                    </div>\
                ';
                $K2(html).appendTo(target);
            }

            break;

        case 'date':
            var label = '',
                time = $K2.now();
            if (!isNewField && currentType == fieldType) {
                label = (fieldValues[0].value ? fieldValues[0].value : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <input name="option_value[]" type="text" id="k2DateField' + time + '" value="' + label + '" autocomplete="off" />\
                    <span class="k2ui-ef-notice">(' + K2Language[1] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);

            // Load Flatpickr
            $K2('#k2DateField' + time).flatpickr({
                allowInput: true
            });
            break;

        case 'image':
            var id = 'K2ExtraFieldImage_' + new Date().getTime(),
                image = '';
            if (!isNewField && currentType == fieldType) {
                image = (fieldValues[0].value ? fieldValues[0].value : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <input name="option_value[]" type="text" id="' + id + '" value="' + image + '" /> <a class="k2app-ef-image-button k2Button" href="index.php?option=com_k2&view=media&type=image&tmpl=component&fieldID=' + id + '">' + K2Language[18] + '</a> <span class="k2ui-ef-notice">(' + K2Language[1] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;

        case 'header':
            var header = '';
            if (!isNewField && currentType == fieldType) {
                header = (fieldValues[0].value ? fieldValues[0].value : '');
            }
            var html = '\
                <div class="k2ui-ef-row">\
                    <div> - </div>\
                    <input name="option_value[]" type="hidden" value="' + header + '" />\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;

        default:
            var html = '\
                <div class="k2ui-ef-row">\
                    <span class="k2ui-ef-notice">(' + K2Language[15] + ')</span>\
                </div>\
            ';
            $K2(html).appendTo(target);
            break;
    }
}

function initExtraFieldsEditor() {
    $K2('.k2ExtraFieldEditor').each(function() {
        var id = $K2(this).attr('id');
        var editorOptions = {};
        var editorHeight = parseInt($K2(this).css('height'));
        if (editorHeight < 100) {
            $K2(this).css('height', 400);
        }
        if (typeof tinymce != 'undefined') {
            // Get Joomla 3.x TinyMCE editor options
            if (K2JVersion === '30' && typeof Joomla.optionsStorage.plg_editor_tinymce !== 'undefined') {
                editorOptions = Joomla.optionsStorage.plg_editor_tinymce.tinyMCE.default;
            }
            // Get JCE editor options
            if (typeof WFEditor !== 'undefined') {
                editorOptions = WFEditor.settings;
            }
            editorOptions.selector = '#' + id;
            editorOptions.width = 'auto';
            // Do not set editorOptions.height as it affects all editor instances in JCE (for some uknown reason)

            if (tinyMCE.get(id)) {
                tinymce.EditorManager.remove(tinyMCE.get(id));
            }
            if (tinymce.majorVersion == 4) {
                tinymce.init(editorOptions);
                tinymce.editors[id].show();
            } else {
                tinyMCE.execCommand('mceAddControl', false, id);
            }
        } else {
            new nicEditor({
                fullPanel: true,
                iconsPath: K2SitePath + 'media/k2/assets/vendors/bkirchoff/nicedit/nicEditorIcons.gif'
            }).panelInstance(id);
            // Properly resize nicEdit
            $K2('.nicEdit-panelContain').parent().css('width', 'calc(90% + 12px)');
            $K2('.nicEdit-panelContain').parent().next().css({
                'width': 'calc(90% + 12px)',
                'min-height': '200px'
            });
            $K2('.nicEdit-main').css('width', 'calc(90% + 12px)');
        }
    });
}

function syncExtraFieldsEditor() {
    $K2('.k2ExtraFieldEditor').each(function() {
        editor = nicEditors.findEditor($K2(this).attr('id'));
        var content = editor && editor.getContent();
        if (typeof editor != 'undefined') {
            if (content == '<br>' || content == '<br />') {
                editor.setContent('');
            }
            editor.saveContent();
        }
    });
    if (K2JVersion === '30') {
        onK2EditorSave();
    }
}

function addAttachment() {
    var timestamp = new Date().getTime();
    $K2('\
    <div class="itemNewAttachment">\
        <button class="removeAttachment k2ui-float-right" title="' + K2Language[0] + '" value=""><i class="fa fa-ban"></i></button>\
        <div class="itemAdditionalField">\
            <div class="k2ui-float-left k2Right itemAdditionalValue">\
                <label>' + K2Language[6] + '</label>\
            </div>\
            <div class="itemAdditionalData">\
                <input type="file" name="attachment[' + timestamp + '][upload]" class="fileUpload k2Selector" />\
                <i>(' + K2Language[7] + ': ' + K2Language[8] + ')</i>\
                <span class="sep">' + K2Language[9] + '</span>\
                <input type="text" name="attachment[' + timestamp + '][existing]" class="text_area existing_file" readonly />\
                <input type="button" value="' + K2Language[10] + '" class="k2AttachmentBrowseServer" />\
            </div>\
        </div>\
        <div class="itemAdditionalField">\
            <div class="k2ui-float-left k2Right itemAdditionalValue">\
                <label>' + K2Language[1] + '</label>\
            </div>\
            <div class="itemAdditionalData">\
                <input type="text" name="attachment[' + timestamp + '][title]" size="30" class="text_area" />\
            </div>\
        </div>\
        <div class="itemAdditionalField">\
            <div class="k2ui-float-left k2Right itemAdditionalValue">\
                <label>' + K2Language[2] + '</label>\
            </div>\
            <div class="itemAdditionalData">\
                <input type="text" name="attachment[' + timestamp + '][title_attribute]" size="30" class="text_area" />\
            </div>\
        </div>\
    </div>\
    ').appendTo($K2('#itemAttachments'));

    $K2('.removeAttachment').on('click', function(e) {
        $K2(this).parent().remove();
    });
}

// Media manager
function elFinderUpdate(fieldID, value) {
    $K2('#' + fieldID).val(value);
    if (typeof window.parent.SqueezeBox.close === 'function') {
        SqueezeBox.close();
    } else {
        parent.$K2('#sbox-window').close();
    }
}

// MFP modal close
function k2ModalClose() {
    $K2(parent.document).magnificPopup('close');
}

// Generic modal selector
function k2ModalSelector(id, name, fid, fname, output) {
    if (output == 'list') {
        // Generic sortable lists
        var exists = false;
        $K2('#' + fid + ' input').each(function() {
            if ($K2(this).val() == id) {
                $K2().k2Alert(K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST.replace('ENTRY_NAME_HERE', name), 3000);
                exists = true;
            }
        });
        if (!exists) {
            var entry = '<li class="handle"><a class="k2EntryRemove" href="#" title="' + K2_REMOVE_THIS_ENTRY + '"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">' + name + '</span><input type="hidden" name="' + fname + '" value="' + id + '" /></li>';
            $K2('#' + fid).append(entry);
            $K2('#' + fid).sortable('refresh');
            $K2('#' + fid + ' .k2EntryRemove').on('click', function(e) {
                e.preventDefault();
                $K2(this).parent().remove();
            });
            $K2().k2Alert(K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST.replace('ENTRY_NAME_HERE', name), 1000);
        }
    } else {
        // Generic single entity
        var exists = false;
        $K2('#' + fid + ' input').each(function() {
            if ($K2(this).val() == id) {
                $K2().k2Alert(K2_THE_ENTRY_IS_ALREADY_IN_THE_LIST.replace('ENTRY_NAME_HERE', name), 3000);
                exists = true;
            }
        });
        if (!exists) {
            var entry = '<div class="handle"><a class="k2EntryRemove" href="#" title="' + K2_REMOVE_THIS_ENTRY + '"><i class="fa fa-trash-o"></i></a><span class="k2EntryText">' + name + '</span><input type="hidden" name="' + fname + '" value="' + id + '" /></div>';
            $K2('#' + fid).html(entry);
            $K2().k2Alert(K2_THE_ENTRY_WAS_ADDED_IN_THE_LIST.replace('ENTRY_NAME_HERE', name), 1000);
            $K2(parent.document).magnificPopup('close');
        }
    }
    return false;
}
