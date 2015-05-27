/**
 * layout creation and management.
 *
 * @class MySched.layout
 * @constructor
 */
var MOBILE_WIDTH_MAX = 400, TABLET_WIDTH_MAX = 1100;

MySched.layout = function ()
{
    "use strict";

    var tabpanel, selectedTab, w_leftMenu, w_topMenu, w_infoPanel, infoWindow;

    return {
        /**
         * Gibt den Ausgewaehlten Tab zurueck
         * TODO: Maybe obsolete, it seems to be never used
         *
         */
        getSelectedTab: function ()
        {
            console.log("MySched.layout getSelectedTab: maybe never used?");
            return this.selectedTab;
        },
        /**
         * Generates the basic layout
         *
         * @mehtod buildLayout
         */
        buildLayout: function ()
        {
            // Creates TabPanel
            this.tabpanel = Ext.create('Ext.tab.Panel',
                {
                    resizeTabs: false,
                    // turn on tab resizing
                    // minTabWidth: 155,
                    // tabWidth: 155,
                    // heigth: 500,
                    enableTabScroll: true,
                    id: 'tabpanel',
                    plugins: [Ext.create('Ext.ux.TabCloseOnMiddleClick')],
                    region: 'center'
                });

            this.tabpanel.on('tabchange',
                function (panel, o)
                {
                    var contentAnchorTip = Ext.getCmp('content-anchor-tip');
                    if (contentAnchorTip)
                    {
                        contentAnchorTip.destroy();
                    }
                    MySched.selectedSchedule = o.ScheduleModel;

                    var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);

                    var currentMoFrDate = getCurrentMoFrDate();
                    var selectedSchedule = MySched.selectedSchedule;
                    var nodeKey = selectedSchedule.key;
                    var nodeID = selectedSchedule.id;
                    var gpuntisID = selectedSchedule.gpuntisID;
                    var semesterID = selectedSchedule.semesterID;
                    var plantypeID = "";
                    var type = selectedSchedule.type;

                    if (MySched.Schedule.status === "unsaved")
                    {
                        Ext.ComponentMgr.get('btnSave').enable();
                    }
                    else
                    {
                        Ext.ComponentMgr.get('btnSave').disable();
                    }

                    if(MySched.selectedSchedule.id !== "mySchedule")
                    {
                        if (MySched.loadLessonsOnStartUp === false)
                        {
                            Ext.Ajax.request(
                                {
                                    url: _C('ajaxHandler'),
                                    method: 'POST',
                                    params: {
                                        nodeID: nodeID,
                                        nodeKey: nodeKey,
                                        gpuntisID: gpuntisID,
                                        semesterID: semesterID,
                                        scheduletask: "Ressource.load",
                                        plantypeID: plantypeID,
                                        type: type,
                                        startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
                                        enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d")
                                    },
                                    failure: function (response)
                                    {
                                        Ext.Msg.alert(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
                                    },
                                    success: function (response)
                                    {
                                        var json = Ext.decode(response.responseText);
                                        var lessonData = json.lessonData;
                                        var lessonDate = json.lessonDate;
                                        for (var item in lessonData)
                                        {
                                            if (Ext.isObject(lessonData[item]))
                                            {
                                                var record = new LectureModel(
                                                    item,
                                                    lessonData[item], semesterID,
                                                    plantypeID);
                                                MySched.Base.schedule.addLecture(record);
                                            }
                                        }
                                        if (Ext.isObject(lessonDate))
                                        {
                                            MySched.Calendar.addAll(lessonDate);
                                        }

                                        MySched.selectedSchedule.eventsloaded = null;
                                        MySched.selectedSchedule.init(type, nodeKey, semesterID);
                                        // Called tab will be reloaded
                                        if (MySched.Schedule.status === "unsaved")
                                        {
                                            Ext.ComponentMgr.get('btnSave').enable();
                                        }
                                        else
                                        {
                                            Ext.ComponentMgr.get('btnSave').disable();
                                        }

                                        var lectureData = MySched.selectedSchedule.data.items;

                                        for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
                                        {
                                            if (Ext.isDefined(lectureData[lectureIndex]) && Ext.isDefined(lectureData[lectureIndex].setCellTemplate) === true)
                                            {
                                                lectureData[lectureIndex].setCellTemplate(MySched.selectedSchedule.type);
                                            }
                                        }

                                        MySched.selectedSchedule.eventsloaded = null;
                                        o.ScheduleModel.refreshView();

                                        // maybe somewhere a hanging AddLectureButton will fade out
                                        // orig.: Evtl. irgendwo haengender AddLectureButton wird ausgeblendet
                                        // TODO serious, who writes such a comment
                                        /* MySched.SelectionManager.selectButton.hide(); */
                                        MySched.SelectionManager.unselect();
                                        this.selectedTab = o;
                                    }
                                }
                            );
                        }
                        else
                        {
                            MySched.selectedSchedule.eventsloaded = null;
                            MySched.selectedSchedule.init(type, nodeKey, semesterID);
                            // Called tab will be reloaded
                            if (MySched.Schedule.status === "unsaved")
                            {
                                Ext.ComponentMgr.get('btnSave').enable();
                            }
                            else
                            {
                                Ext.ComponentMgr.get('btnSave').disable();
                            }

                            var lectureData = MySched.selectedSchedule.data.items;

                            for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
                            {
                                if (Ext.isDefined(lectureData[lectureIndex]) && Ext.isDefined(lectureData[lectureIndex].setCellTemplate) === true)
                                {
                                    lectureData[lectureIndex].setCellTemplate(MySched.selectedSchedule.type, MySched.selectedSchedule.scheduleGrid);
                                }
                            }

                            MySched.selectedSchedule.eventsloaded = null;
                            o.ScheduleModel.refreshView();

                            // maybe somewhere a hanging AddLectureButton will fade out
                            // orig.: Evtl. irgendwo haengender AddLectureButton wird ausgeblendet
                            // no comment...
                            /* MySched.SelectionManager.selectButton.hide(); */
                            MySched.SelectionManager.unselect();
                            this.selectedTab = o;
                        }
                    }
                    else
                    {
                        var lectureData = MySched.Schedule.data.items;

                        for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
                        {
                            if (Ext.isDefined(lectureData[lectureIndex]) && Ext.isDefined(lectureData[lectureIndex].setCellTemplate) === true)
                            {
                                lectureData[lectureIndex].setCellTemplate(MySched.Schedule.type, MySched.Schedule.scheduleGrid);
                            }
                        }
                        MySched.Schedule.refreshView();
                    }
                }, this
            );

            // if the header of the THM should be displayed
            if (_C('showHeader'))
            {
                this.w_topMenu = Ext.create('Ext.Panel',
                    {
                        id: 'topMenu',
                        region: 'north',
                        bodyStyle: 'text-align:center;',
                        html: _C('headerHTML'),
                        bbar: this.getMainToolbar()
                    });
                // ..and if not
            }
            else
            {
                this.w_topMenu = Ext.create('Ext.Panel',
                    {
                        id: 'topMenu',
                        region: 'north',
                        bbar: this.getMainToolbar()
                    });
            }

            //var treeData = MySched.Tree.init();
            var treeData = MySched.SelectBoxes.init();

            this.w_leftMenu = treeData;

            this.w_leftMenu.on("expand", function ()
            {
                if (MySched.selectedSchedule)
                {
                    MySched.selectedSchedule.eventsloaded = null;
                    MySched.selectedSchedule.refreshView();
                }
            });

            this.w_leftMenu.on("collapse", function ()
            {
                if (MySched.selectedSchedule)
                {
                    MySched.selectedSchedule.eventsloaded = null;
                    MySched.selectedSchedule.refreshView();
                }
            });


            this.rightviewport = Ext.create(
                'Ext.Panel',
                {
                    id: "rightviewport",
                    region: 'center',
                    items: [this.w_topMenu, this.tabpanel]
                }
            );

            this.leftviewport = this.w_leftMenu;
            // 	finally, creation of the complete layout
            this.viewport = Ext.create('Ext.panel.Panel',
                {
                    plugins: 'responsive',
                    id: "viewport",
                    layout: "border",
                    renderTo: "MySchedMainW",
                    width: 968,
                    height: 500,
                    //minSize: 968,
                    //maxSize: 968,
                    items: [this.leftviewport, this.rightviewport],
                    responsiveConfig: {
                        'width <= TABLET_WIDTH_MAX':
                        {
                            // TODO What is a useful max width
                            maxWidth: document.getElementById('MySchedMainW').clientWidth
                        }
                    }
                });

            var hideTreePanel = false;
            if(MySched.schedulerFromMenu === false)
            {
                hideTreePanel = true;
            }

            Ext.get('selectBoxes-body').mask('Loading');

            var calendar = Ext.ComponentMgr.get('menuedatepicker'), imgs;
            if (calendar)
            {
                imgs = Ext.DomQuery.select('img[class=x-form-trigger x-form-date-trigger]', calendar.container.dom);
            }
            for (var i = 0; i < imgs.length; i++)
            {
                imgs[i].alt = "calendar";
            }
        },
        /**
         * Shows the information window of MySched
         *
         * @method showInfoWindow
         */
        showInfoWindow: function ()
        {
            if (Ext.ComponentMgr.get("infoWindow") === null || typeof Ext.ComponentMgr.get("infoWindow") === "undefined")
            {

                this.infoWindow = Ext.create('Ext.Window',
                    {
                        id: 'infoWindow',
                        title: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFO_WINDOW_TITLE,
                        width: 675,
                        height: 380,
                        frame: false,
                        bodyStyle: 'background-color: #FFF; padding: 7px;',
                        buttons: [
                            {
                                text: 'Schlie&szlig;en',
                                handler: function ()
                                {
                                    this.infoWindow.close();
                                },
                                scope: this
                            }],
                        html: "<small style='float:right; font-style:italic;'>Version " + MySched.version + "</small>" + "<p style='font-weight:bold;'>&Auml;nderungen sind farblich markiert: <br /> <p style='padding-left:10px;'> <span style='background-color: #00ff00;' >Neue Veranstaltung</span></p> <p style='padding-left:10px;'><span style='background-color: #ff4444;' >Gel&ouml;schte Veranstaltung</span></p> <p style='padding-left:10px;'><span style='background-color: #ffff00;' >Ge&auml;nderte Veranstatung (neuer Raum, neuer Dozent)</span> </p><p style='padding-left:10px;'> <span style='background-color: #ffaa00;' >Ge&auml;nderte Veranstaltung (neue Zeit:von)</span>, <span style='background-color: #ffff00;' >Ge&auml;nderte Veranstaltung (neue Zeit:zu)</span></p></p>" + "<b>Version: 2.1.6:</b>" + "<ul>" + "<li style='padding-left:10px;'>NEU: Hinzuf&uuml;gen der Veranstaltungen &uuml;ber Kontextmenu (Rechtsklick auf Veranstaltung).</li>" + "<li style='padding-left:10px;'>NEU: Hinzuf&uuml;gen von eigenen Veranstaltungen &uuml;ber Kontextmenu (Rechtsklick in einen Block).</li>" + "<li style='padding-left:10px;'>NEU: Navigation &uuml;ber den Dozent, Raum, Fachbereich einer Veranstaltung.</li>" + "<li style='padding-left:10px;'>NEU: Navigation durch einzelne Wochen &uuml;ber einen Kalender (Men&uuml;leiste).</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Anzeige von Terminen.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Informationen zu Terminen &uuml;ber Termintitel (Mauszeiger &uuml;ber Titel).</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Informationen zu Veranstaltungen &uuml;ber Veranstaltungstitel (Klick auf den Titel).</li>" + "</ul>" + "<br/>" + "<b>Version: 2.1.5:</b>" + "<ul>" + "<li style='padding-left:10px;'>NEU: Pers&ouml;nliche Termine k&ouml;nnen &uuml;ber den Men&uuml;punkt 'Neuer Termin' oder per Klick in einen Block angelegt werden.</li>" + "<li style='padding-left:10px;'>NEU: Berechtigte Benutzer d&uuml;rfen im Panel 'Einzel Termine' neue Termine anlegen oder alte editieren.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: &Auml;nderungen werden wie ein Stundenplan aufgerufen.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Nur registrierte Benutzer haben Zugriff auf alle Funktionen.</li>" + "</ul>" + "<br/>" + "<b>Version: 2.1.4:</b>" + "<li style='padding-left:10px;'>NEU: In der Infoanzeige von Veranstaltungen kann die Modulbeschreibung abgerufen werden.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Termine werden nur noch an betroffenen Tagen angezeigt.</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: Termine werden bei Klick auf das orangene Ausrufezeichen angezeigt.</li>" + "<br/>" + "<b>Version: 2.1.3:</b>" + "" + "<li style='padding-left:10px;'>NEU: Stundenpl&auml;ne k&ouml;nnen als Terminkalendar heruntergeladen werden. (Men&uuml;punkt ICal Download)</li>" + "<li style='padding-left:10px;'>NEU: Navigationsleiste kann eingeklappt werden.</li>" + "<li style='padding-left:10px;'>NEU: Veranstaltungen k&ouml;nnen per Doppelklick hinzugef&uuml;gt / entfernt werden.</li>" + "<li style='padding-left:10px;'>NEU: Bei &Auml;nderungen zu Ihrem abgespeicherten Plan werden jetzt sinnvolle Vorschl&auml;ge gemacht.</li>" + "<li style='padding-left:10px;'>NEU: Kontrastreiche Men&uuml;s, sinnvollere Neuanordnung des Men&uuml;s.</li>" + "<li style='padding-left:10px;'>NEU: Seitentitel auch als Titel des pdf-download.</li>" + "<li style='padding-left:10px;'>NEU: Kleinere Texte bei den Einzelterminen.</li>" + "<br/>" + "<b>Version: 2.1.2:</b>" + "" + "<li style='padding-left:10px;'>NEU: MNI Style</li>" + "<li style='padding-left:10px;'>GE&Auml;NDERT: PDF Download und PDF Dateiname bezieht sich auf den aktiven Tab</li>"
                    });
                this.infoWindow.show();
            }
        },
        /**
         * Creates a new schedule tab
         *
         * @param {string} id ID of the tab
         * @param {string} title Title of the Tabs
         * @param {Object} grid The Grid that should be shown
         * @param {String} type the type
         * @param {Boolean} closeable wWether or not the element can be closed
         */
        createTab: function (id, title, grid, type, closeable)
        {
            console.log(grid);
            if (closeable !== false)
            {
                closeable = true;
            }
            var tab = null;
            if (MySched.Authorize.role !== "user" || id !== "mySchedule")
            {
                if (!(tab = this.tabpanel.getComponent(id)))
                {
                    if (type)
                    {

                        var lectureData = grid.ScheduleModel.data.items;

                        for (var lectureIndex = 0; lectureIndex < lectureData.length; lectureIndex++)
                        {
                            if (Ext.isDefined(lectureData[lectureIndex]))
                            {
                                if (Ext.isDefined(lectureData[lectureIndex].setCellTemplate) === true)
                                {
                                    lectureData[lectureIndex].setCellTemplate(type, grid.ScheduleModel.scheduleGrid);
                                }
                            }
                        }
                    }
                    if ((MySched.Authorize.role === "user" && type === "delta") || type === "mySchedule")
                    {
                        tab = Ext.apply(
                            // default values - if already set they keep
                            Ext.apply(grid,
                                {
                                    cls: 'MySched_ScheduleTab',
                                    closable: false
                                }),
                            {
                                // They will be overwritten if they exist
                                id: id,
                                title: title
                            });
                    }
                    else
                    {

                        console.log(id);
                        console.log(grid);
                        tab = Ext.apply(
                            // default values - if already set they keep
                            Ext.apply(grid,
                                {
                                    cls: 'MySched_ScheduleTab',
                                    closable: closeable,
                                    title: title
                                    // iconCls: type + 'Icon',
                                }),
                            {
                                // They will be overwritten if they exist
                                // TODO: There is a problem with the ID!!!
                                //id: id,
                                title: title
                            });
                    }
                    console.log(tab);
                    this.tabpanel.add(tab);
                }
                if (Ext.getCmp('content-anchor-tip'))
                {
                    Ext.getCmp('content-anchor-tip').destroy();
                }

                MySched.selectedSchedule = tab.ScheduleModel;
                // The called tab will be reloaded
                if (MySched.Schedule.status === "unsaved")
                {
                    Ext.ComponentMgr.get('btnSave').enable();
                }
                else
                {
                    Ext.ComponentMgr.get('btnSave').disable();
                }

                MySched.selectedSchedule.eventsloaded = null;
                // tab.ScheduleModel.refreshView();

                this.selectedTab = tab;

                // Switch to the new created tab
                this.tabpanel.setActiveTab(tab);
                MySched.Base.regScheduleEvents(id);

                if (type !== "mySchedule")
                {
                    var tabData = {};
                    tabData.id = tab.ScheduleModel.id;
                    tabData.nodeKey = tab.ScheduleModel.id;
                    tabData.gpuntisID = tab.ScheduleModel.gpuntisID;
                    tabData.semesterID = tab.ScheduleModel.semesterID;
                    tabData.type = tab.ScheduleModel.type;
                    tabData.nodeKey = tab.ScheduleModel.key;

                    var tabBar = this.tabpanel.getTabBar();
                    initializePatientDragZone(tabBar.activeTab, tabData);
                }

                if(this.tabpanel.items.length === 1)
                {
                    MySched.selectedSchedule.refreshView();
                }
            }
        },
        /**
         * Gives the toolbar back.
         * It creates all buttons and the calendar of the toolbar with its handler and style attributes
         *
         * @method getMainToolbar
         * @return {array} * Returns all buttons of the toolbar
         */
        getMainToolbar: function ()
        {
            // Create the save schedule button
            var btnSave = Ext.create(
                'Ext.Button',
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SAVE,
                    id: 'btnSave',
                    iconCls: 'tbSave',
                    disabled: false,
                    hidden: true,
                    handler: MySched.Authorize.saveIfAuth,
                    scope: MySched.Authorize,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_SAVE
                    }
                }
            );
            // Create the "empty the schedule" button
            var btnEmpty = Ext.create(
                'Ext.Button',
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_EMPTY,
                    id: 'btnEmpty',
                    iconCls: 'tbEmpty',
                    hidden: true,
                    disabled: false,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_EMPTY
                    },
                    scope: MySched.selectedSchedule,
                    // This handler first asks the user if he want to empty his schedule
                    // Afterwards all lectures are removed from my schedule
                    handler: function ()
                    {
                        Ext.Msg.confirm(
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE,
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE_QUESTION1
                            + MySched.selectedSchedule.title
                            + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_LESSON_DELETE_QUESTION2,

                            function (r)
                            {
                                if (r === 'yes')
                                {
                                    var lessons = MySched.selectedSchedule.getLectures();
                                    var toremove = [];
                                    var i;
                                    for (i = 0; i < lessons.length; i++)
                                    {
                                        if ((lessons[i].data.type === "personal" && (lessons[i].data.owner === MySched.Authorize.user && lessons[i].data.responsible === MySched.selectedSchedule.id)) || MySched.selectedSchedule.id === "mySchedule")
                                        {
                                            toremove[toremove.length] = lessons[i].data.key;
                                        }
                                    }
                                    for (i = 0; i < toremove.length; i++)
                                    {
                                        if (MySched.selectedSchedule.id === "mySchedule")
                                        {
                                            MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(toremove[i]));
                                        }
                                        else
                                        {
                                            var tab = MySched.layout.tabpanel.getComponent(MySched.Base.schedule.getLecture(toremove[i]).data.responsible);
                                            if (tab)
                                            {
                                                tab.ScheduleModel.removeLecture(tab.ScheduleModel.getLecture(toremove[i]));
                                            }
                                            MySched.selectedSchedule.removeLecture(MySched.selectedSchedule.getLecture(toremove[i]));
                                            MySched.Schedule.removeLecture(MySched.Schedule.getLecture(toremove[i]));
                                            MySched.responsibleChanges.removeLecture(MySched.responsibleChanges.getLecture(toremove[i]));
                                            MySched.Base.schedule.removeLecture(MySched.Base.schedule.getLecture(toremove[i]));
                                        }
                                    }
                                    MySched.selectedSchedule.eventsloaded = null;
                                    MySched.selectedSchedule.refreshView();
                                }
                            }
                        );
                    }
                }
            );

            var disablePDF = true;
            if(MySched.FPDFInstalled)
            {
                disablePDF = false;
            }

            // Export the schedule to pdf button
            var btnSavePdf = Ext.create(
                'Ext.Button',
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE,
                    id: 'btnPdf',
                    iconCls: 'tbSavePdf',
                    disabled: disablePDF,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_PDF_DESC
                    },
                    handler: function ()
                    {
                        clickMenuHandler();
                        var pdfwait = Ext.MessageBox.wait(
                            "",
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_CREATE,
                            {
                                interval: 100,
                                duration: 2000
                            });

                        var currentMoFrDate = getCurrentMoFrDate();

                        Ext.Ajax.request(
                            {
                                url: _C('ajaxHandler'),
                                jsonData: MySched.selectedSchedule.exportData("jsonpdf"),
                                method: 'POST',
                                params: {
                                    username: MySched.Authorize.user,
                                    title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                                    what: "pdf",
                                    startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
                                    enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d"),
                                    scheduletask: "Schedule.export",
                                    semesterID: MySched.selectedSchedule.semesterID
                                },
                                scope: pdfwait,
                                failure: function ()
                                {
                                    Ext.MessageBox.hide();
                                    Ext.Msg.alert(
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                },
                                success: function (response)
                                {
                                    Ext.MessageBox.hide();
                                    if (response.responseText !== "Permission Denied!")
                                    {
                                        // Iframe for download will be created
                                        Ext.core.DomHelper.append(
                                            Ext.getBody(),
                                            {
                                                tag: 'iframe',
                                                id: 'downloadIframe',
                                                src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=pdf&save=false&scheduletask=Download.schedule",
                                                style: 'display:none;z-index:10000;'
                                            });
                                        // Iframe will be deleted after 2 sec
                                        var func = function ()
                                        {
                                            Ext.get('downloadIframe')
                                                .remove();
                                        };
                                        Ext.defer(func, 2000);
                                    }
                                    else
                                    {
                                        Ext.Msg.alert(
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                    }
                                }
                            });
                    }
                }
            );

            // Save schedule of the week in pdf button
            var btnSaveWeekPdf = Ext.create(
                'Ext.Button',
                {
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_WEEK_SCHEDULE,
                    id: 'btnWeekPdf',
                    iconCls: 'tbSavePdf',
                    disabled: disablePDF,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_WEEK_SCHEDULE_PDF_DESC
                    },
                    handler: function ()
                    {
                        clickMenuHandler();
                        var pdfwait = Ext.MessageBox.wait(
                            "",
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_CREATE,
                            {
                                interval: 100,
                                duration: 2000
                            });

                        Ext.Ajax.request(
                            {
                                url: _C('ajaxHandler'),
                                jsonData: MySched.selectedSchedule.exportAllData(true),
                                method: 'POST',
                                params: {
                                    username: MySched.Authorize.user,
                                    title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                                    what: "pdf",
                                    scheduletask: "Schedule.export"
                                },
                                scope: pdfwait,
                                failure: function ()
                                {
                                    Ext.MessageBox.hide();
                                    Ext.Msg.alert(
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                },
                                success: function (response)
                                {
                                    Ext.MessageBox.hide();
                                    if (response.responseText !== "Permission Denied!")
                                    {
                                        // Iframe for download will be created
                                        Ext.core.DomHelper.append(
                                            Ext.getBody(),
                                            {
                                                tag: 'iframe',
                                                id: 'downloadIframe',
                                                src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=pdf&save=false&scheduletask=Download.schedule",
                                                style: 'display:none;z-index:10000;'
                                            });
                                        // Iframe will be deleted after 2 sec
                                        var func = function ()
                                        {
                                            Ext.get('downloadIframe')
                                                .remove();
                                        };
                                        Ext.defer(func, 2000);
                                    }
                                    else
                                    {
                                        Ext.Msg.alert(
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                    }
                                }
                            }
                        );
                    }
                }
            );

            var disableICal = true;
            if(MySched.iCalcreatorInstalled)
            {
                disableICal = false;
            }

            // Button for download schedule as ical
            var btnICal = Ext.create(
                'Ext.Button',
                {
                    // ICal DownloadButton
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ICAL,
                    id: 'btnICal',
                    iconCls: 'tbSaveICal',
                    disabled: disableICal,
                    tooltip: { text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ICAL_DESC },
                    handler: function ()
                    {
                        clickMenuHandler();
                        var icalwait = Ext.MessageBox.wait(
                            "",
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_CREATE,
                            {
                                interval: 100,
                                duration: 2000
                            }
                        );
                        Ext.Ajax.request(
                            {
                                url: _C('ajaxHandler'),
                                jsonData: MySched.selectedSchedule.exportData(),
                                method: 'POST',
                                params: {
                                    username: MySched.Authorize.user,
                                    title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                                    what: "ical",
                                    scheduletask: "Schedule.export",
                                    departmentAndSemester: MySched.departmentAndSemester
                                },
                                scope: icalwait,
                                failure: function (response, ret)
                                {
                                    Ext.MessageBox.hide();
                                    Ext.Msg.alert(
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR
                                    );
                                },
                                success: function (response, ret)
                                {
                                    Ext.MessageBox.hide();
                                    try
                                    {
                                        var responseData = [];
                                        responseData = Ext.decode(response.responseText);
                                        if (responseData.url !== "false")
                                        {
                                            Ext.MessageBox.show(
                                                {
                                                    minWidth: 500,
                                                    title: "Synchronisieren",
                                                    msg: '<strong style="font-weight:bold">Link</strong>:<br/>' + responseData.url + '<br/>Wollen Sie den Terminkalendar ersetzen?',
                                                    buttons: Ext.Msg.YESNO,
                                                    fn: function (btn,text)
                                                    {
                                                        var func;
                                                        if (btn === "yes")
                                                        {
                                                            // Iframe for download will be created
                                                            Ext.core.DomHelper.append(
                                                                Ext.getBody(),
                                                                {
                                                                    tag: 'iframe',
                                                                    id: 'downloadIframe',
                                                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=true&scheduletask=Download.schedule",
                                                                    style: 'display:none;z-index:10000;'
                                                                });
                                                            // Iframe will be deleted after 2 sec
                                                            func = function ()
                                                            {
                                                                Ext.get('downloadIframe').remove();
                                                            };
                                                            Ext.defer(func, 2000);
                                                        }
                                                        else
                                                        {
                                                            // Iframe for download will be created
                                                            Ext.core.DomHelper.append(
                                                                Ext.getBody(),
                                                                {
                                                                    tag: 'iframe',
                                                                    id: 'downloadIframe',
                                                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=false&scheduletask=Download.schedule",
                                                                    style: 'display:none;z-index:10000;'
                                                                }
                                                            );
                                                            // Iframe will be deleted after 2 sec
                                                            func = function ()
                                                            {
                                                                Ext.get('downloadIframe').remove();
                                                            };
                                                            Ext.defer(func, 2000);
                                                        }
                                                    }
                                                }
                                            );
                                        }
                                        else
                                        {
                                            // Iframe for download will be created
                                            Ext.core.DomHelper.append(
                                                Ext.getBody(),
                                                {
                                                    tag: 'iframe',
                                                    id: 'downloadIframe',
                                                    src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=ics&save=false&scheduletask=Download.schedule",
                                                    style: 'display:none;z-index:10000;'
                                                });
                                            // Iframe will be deleted after 2 sec
                                            var func = function ()
                                            {
                                                Ext.get('downloadIframe').remove();
                                            };
                                            Ext.defer(func, 2000);
                                        }
                                    }
                                    catch (e)
                                    {
                                        Ext.Msg.alert(
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                    }
                                }
                            }
                        );
                    }
                }
            );

            var disableExcel = true;
            if(MySched.PHPExcelInstalled)
            {
                disableExcel = false;
            }

            // Button for saving schedule as excel format
            var btnSaveTxt = Ext.create(
                'Ext.Button',
                {
                    // TxT DownloadButton
                    text: MySchedLanguage.COM_THM_ORGANIZER_ACTION_EXPORT_EXCEL,
                    id: 'btnTxt',
                    iconCls: 'tbSaveTxt',
                    disabled: disableExcel,
                    tooltip: { text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_EXCEL_DESC },
                    handler: function ()
                    {
                        clickMenuHandler();
                        var txtwait = Ext.MessageBox.wait(
                            "",
                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_CREATE,
                            {
                                interval: 100,
                                duration: 2000
                            }
                        );

                        Ext.Ajax.request(
                            {
                                url: _C('ajaxHandler'),
                                jsonData: MySched.selectedSchedule.exportAllData(),
                                method: 'POST',
                                params: {
                                    username: MySched.Authorize.user,
                                    title: MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' '),
                                    what: "ics",
                                    scheduletask: "Schedule.export"
                                },
                                scope: txtwait,
                                failure: function ()
                                {
                                    Ext.MessageBox.hide();
                                    Ext.Msg.alert(
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                },
                                success: function (response)
                                {
                                    Ext.MessageBox.hide();
                                    if (response.responseText !== "Permission Denied!")
                                    {
                                        // Iframe for download will be created
                                        Ext.core.DomHelper.append(
                                            Ext.getBody(),
                                            {
                                                tag: 'iframe',
                                                id: 'downloadIframe',
                                                src: _C('ajaxHandler') + '&username=' + MySched.Authorize.user + "&title=" + encodeURIComponent(MySched.selectedSchedule.title.replace(/\s*\/\s*/g, ' ')) + "&what=xls&save=false&scheduletask=Download.schedule",
                                                style: 'display:none;z-index:10000;'
                                            });
                                        // Iframe will be deleted after 2 sec
                                        var func = function ()
                                        {
                                            Ext.get('downloadIframe').remove();
                                        };
                                        Ext.defer(func, 2000);
                                    }
                                    else
                                    {
                                        Ext.Msg.alert(
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                                            MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_ERROR);
                                    }
                                }
                            }
                        );
                    }
                }
            );

            // create event Button
            var btnAdd = Ext.create('Ext.Button',
                {
                    // addButton
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD,
                    id: 'btnAdd',
                    iconCls: 'tbAdd',
                    disabled: true,
                    hidden: true,
                    handler: MySched.SelectionManager.lecture2ScheduleHandler,
                    scope: MySched.SelectionManager,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ADD_LESSON
                    }
                }
            );

            // create menu button
            var btnMenu = Ext.create('Ext.Button',
                {
                    // MenuButton
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD,
                    id: 'btnMenu',
                    iconCls: 'tbDownload',
                    disabled: false,
                    clicked: false,
                    menu: [btnSavePdf, btnSaveWeekPdf, btnICal, btnSaveTxt]
                }
            );

            function clickMenuHandler()
            {
                btnMenu.hideMenu();
            }

            // create clear button
            var btnDel = Ext.create('Ext.Button',
                {
                    // clearButton
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_REMOVE,
                    id: 'btnDel',
                    iconCls: 'tbTrash',
                    hidden: true,
                    disabled: true,
                    handler: MySched.SelectionManager.lecture2ScheduleHandler,
                    scope: MySched.SelectionManager,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_REMOVE_LESSON
                    }
                }
            );

            // create info button
            var btnInfo = Ext.create(
                'Ext.Button',
                {
                    // InfoButton
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_INFO,
                    id: 'btnInfo',
                    iconCls: 'tbInfo',
                    hidden: true,
                    handler: MySched.layout.showInfoWindow,
                    scope: MySched.layout
                }
            );

            // Create free/busy button
            var tbFreeBusy = Ext.create('Ext.Button',
                {
                    // Free/busy Button
                    text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_FREE_BUSY,
                    id: 'btnFreeBusy',
                    iconCls: 'tbFreeBusy',
                    hidden: true,
                    enableToggle: true,
                    pressed: MySched.freeBusyState,
                    toggleHandler: MySched.Base.freeBusyHandler,
                    tooltip: {
                        text: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_FREE_BUSY_DESC
                    }
                }
            );

            Ext.DatePicker.prototype.startDay = 1;

            var inidate = new Date();

            // create calendar
            var menuedatepicker = Ext.create(
                'Ext.form.field.Date',
                {
                    plugins: 'responsive',
                    id: 'menuedatepicker',
                    showWeekNumber: true,
                    format: 'd.m.Y',
                    useQuickTips: false,
                    editable: false,
                    value: inidate,
                    startDay: 1,
                    responsiveConfig: {
                        'width <= TABLET_WIDTH_MAX': {
                            disabled: true
                        }
                    },
                    // disabledDays: [0, 6],
                    listeners: {
                        'change': function ()
                        {
                            if (MySched.selectedSchedule !== null)
                            {
                                if(MySched.selectedSchedule.id !== "mySchedule")
                                {
                                    var weekpointer = Ext.Date.clone(Ext.ComponentMgr.get('menuedatepicker').value);

                                    var currentMoFrDate = getCurrentMoFrDate();
                                    var selectedSchedule = MySched.selectedSchedule;
                                    var nodeKey = selectedSchedule.key;
                                    var nodeID = selectedSchedule.id;
                                    var gpuntisID = selectedSchedule.gpuntisID;
                                    var semesterID = selectedSchedule.semesterID;
                                    var plantypeID = "";
                                    var type = selectedSchedule.type;

                                    if (MySched.loadLessonsOnStartUp === false)
                                    {
                                        Ext.Ajax.request(
                                            {
                                                url: _C('ajaxHandler'),
                                                method: 'POST',
                                                params: {
                                                    nodeID: nodeID,
                                                    nodeKey: nodeKey,
                                                    gpuntisID: gpuntisID,
                                                    semesterID: semesterID,
                                                    scheduletask: "Ressource.load",
                                                    plantypeID: plantypeID,
                                                    type: type,
                                                    startdate: Ext.Date.format(currentMoFrDate.monday, "Y-m-d"),
                                                    enddate: Ext.Date.format(currentMoFrDate.friday, "Y-m-d")
                                                },
                                                failure: function (response)
                                                {
                                                    Ext.Msg.alert(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_ERROR,
                                                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_SCHEDULE_ERROR);
                                                },
                                                success: function (response)
                                                {
                                                    var json = Ext.decode(response.responseText);
                                                    var lessonData = json.lessonData;
                                                    var lessonDate = json.lessonDate;
                                                    for (var item in lessonData)
                                                    {
                                                        if (Ext.isObject(lessonData[item]))
                                                        {
                                                            var record = new LectureModel(
                                                                item,
                                                                lessonData[item], semesterID,
                                                                plantypeID);
                                                            MySched.Base.schedule.addLecture(record);
                                                        }
                                                    }
                                                    if (Ext.isObject(lessonDate))
                                                    {
                                                        MySched.Calendar.addAll(lessonDate);
                                                    }

                                                    MySched.selectedSchedule.eventsloaded = null;
                                                    MySched.selectedSchedule.init(type, nodeKey, semesterID);
                                                    MySched.selectedSchedule.refreshView();
                                                }
                                            }
                                        );
                                    }
                                    else
                                    {
                                        MySched.selectedSchedule.eventsloaded = null;
                                        MySched.selectedSchedule.init(type, nodeKey, semesterID);
                                        MySched.selectedSchedule.refreshView();
                                    }
                                }
                                else
                                {
                                    MySched.selectedSchedule.eventsloaded = null;
                                    MySched.selectedSchedule.refreshView();
                                }
                            }
                        }
                    }
                }
            );

            menuedatepicker.on('expand', function(me, e) {
                var calendarPicker = me.getPicker();
                calendarPicker.el.dom.firstChild.title = "";
            }, null, {single: true});

            // previous week button
            var prevWeek = {
                plugins: 'responsive',
                id: 'MySched_prevWeek',
                tooltip: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DISPLAY_PREVIOUS_WEEK,
                handler: function()
                {
                    var calendar = Ext.ComponentMgr.get('menuedatepicker');
                    var currentDate = calendar.getValue();
                    currentDate.setDate(currentDate.getDate() - 7);
                    calendar.setValue(currentDate);
                },
                scope: this,
                iconCls: 'MySched_prevWeekIcon',
                responsiveConfig: {
                    'width <= TABLET_WIDTH_MAX': {
                        hidden: false
                    }
                },
            };


            // next week button
            var nextWeek = {
                plugins: 'responsive',
                id: 'MySched_nextWeek',
                tooltip: MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_DISPLAY_NEXT_WEEK,
                handler: function()
                {
                    var calendar = Ext.ComponentMgr.get('menuedatepicker');
                    var currentDate = calendar.getValue();
                    currentDate.setDate(currentDate.getDate() + 7);
                    calendar.setValue(currentDate);
                },
                scope: this,
                iconCls: 'MySched_nextWeekIcon',
                responsiveConfig: {
                    'width <= TABLET_WIDTH_MAX': {
                        hidden: false
                    }
                }
            };

            return [prevWeek, menuedatepicker, nextWeek, btnSave, btnMenu, '->', btnInfo, btnEmpty, btnAdd, btnDel];
        }
    };
}();


/**
 * Function to handle to drag and drop
 *
 * @param {object} dragElement The elements that are draggable
 * @param {object} tabData Information like id of the tab
 */
function initializePatientDragZone(dragElement, tabData) {
    dragElement.dragZone = Ext.create('Ext.dd.DragZone', dragElement.getEl(), {

        ddGroup: 'lecture',
        containerScroll: true,
        /**
         * On receipt of a mousedown event, see if it is within a draggable element.
         * Return a drag data object if so. The data object can contain arbitrary application
         * data, but it should also contain a DOM element in the ddel property to provide a proxy to drag.
         *
         * @method getDragData
         * @param {object} e The mouse event
         * @return {object} * TODO
         */
        getDragData: function(e) {
            var sourceEl = e.currentTarget;
            if (sourceEl) {
                var d = sourceEl.cloneNode(true);
                d.id = Ext.id();
                d.style.left = 0;

                dragElement.dragData = {
                    sourceEl: sourceEl,
                    repairXY: Ext.fly(sourceEl).getXY(),
                    ddel: d,
                    patientData: tabData
                };
                return dragElement.dragData;
            }
        },
        /**
         * Provide coordinates for the proxy to slide back to on failed drag.
         * This is the original XY coordinates of the draggable element.
         *
         * @method getRepairXY
         * @return {array} * X and Y coordinates
         */
        getRepairXY: function() {
            return this.dragData.repairXY;
        }
    });
}