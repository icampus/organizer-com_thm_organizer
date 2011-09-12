Ext.override(Ext.picker.Date, {
	fullUpdate: function(date, active){
        var me = this,
            cells = me.cells.elements,
            textNodes = me.textNodes,
            disabledCls = me.disabledCellCls,
            eDate = Ext.Date,
            i = 0,
            extraDays = 0,
            visible = me.isVisible(),
            sel = +eDate.clearTime(date, true),
            today = +eDate.clearTime(new Date()),
            min = me.minDate ? eDate.clearTime(me.minDate, true) : Number.NEGATIVE_INFINITY,
            max = me.maxDate ? eDate.clearTime(me.maxDate, true) : Number.POSITIVE_INFINITY,
            ddMatch = me.disabledDatesRE,
            ddText = me.disabledDatesText,
            ddays = me.disabledDays ? me.disabledDays.join('') : false,
            ddaysText = me.disabledDaysText,
            format = me.format,
            days = eDate.getDaysInMonth(date),
            firstOfMonth = eDate.getFirstDateOfMonth(date),
            startingPos = firstOfMonth.getDay() - me.startDay,
            previousMonth = eDate.add(date, eDate.MONTH, -1),
            longDayFormat = me.longDayFormat,
            prevStart,
            current,
            disableToday,
            tempDate,
            setCellClass,
            html,
            cls,
            formatValue,
            value;

        if (startingPos < 0) {
            startingPos += 7;
        }

        days += startingPos;
        prevStart = eDate.getDaysInMonth(previousMonth) - startingPos;
        current = new Date(previousMonth.getFullYear(), previousMonth.getMonth(), prevStart, me.initHour);

        if (me.showToday) {
            tempDate = eDate.clearTime(new Date());
            disableToday = (tempDate < min || tempDate > max ||
                (ddMatch && format && ddMatch.test(eDate.dateFormat(tempDate, format))) ||
                (ddays && ddays.indexOf(tempDate.getDay()) != -1));

            if (!me.disabled) {
                me.todayBtn.setDisabled(disableToday);
                me.todayKeyListener.setDisabled(disableToday);
            }
        }

        setCellClass = function(cell){
            value = +eDate.clearTime(current, true);
            //cell.title = eDate.format(current, longDayFormat);
            cell.title = ' ';
            //store dateValue number as an expando
            cell.firstChild.dateValue = value;
            if(value == today){
                cell.className += ' ' + me.todayCls;
                cell.title = me.todayText;
            }
            if(value == sel){
                cell.className += ' ' + me.selectedCls;
                me.el.dom.setAttribute('aria-activedescendant', cell.id);
                if (visible && me.floating) {
                    Ext.fly(cell.firstChild).focus(50);
                }
            }
            // disabling
            if(value < min) {
                cell.className = disabledCls;
                cell.title = me.minText;
                return;
            }
            if(value > max) {
                cell.className = disabledCls;
                cell.title = me.maxText;
                return;
            }
            if(ddays){
                if(ddays.indexOf(current.getDay()) != -1){
                    cell.title = ddaysText;
                    cell.className = disabledCls;
                }
            }
            if(ddMatch && format){
                formatValue = eDate.dateFormat(current, format);
                if(ddMatch.test(formatValue)){
                    cell.title = ddText.replace('%0', formatValue);
                    cell.className = disabledCls;
                }
            }

            var begin = MySched.session["begin"].split(".");
			begin = new Date(begin[2], begin[1]-1, begin[0]);
			var end = MySched.session["end"].split(".");
			end = new Date(end[2], end[1]-1, end[0]);

			cell.children[0].events = new Array();

			begin.setHours(current.getHours());
			end.setHours(current.getHours());

			if(current >= begin && current <= end)
			{
				var len = cell.children[0].events.length;
				if(current.compare(begin) === 0)
					cell.children[0].events[len] = "Semesteranfang";
				else if(current.compare(end) === 0)
					cell.children[0].events[len] = "Semesterende";
				else
					cell.children[0].events[len] = "Semester";

				cell.className += " MySched_Semester";
				if (!cell.children[0].className.contains(" calendar_tooltip")) cell.children[0].className += " calendar_tooltip";
			}

			var EL = MySched.eventlist.data;

			for (var ELindex = 0; ELindex < EL.length; ELindex++) {

				var startdate = EL.items[ELindex].data.startdate.split(".");
				startdate = new Date(startdate[2], startdate[1]-1, startdate[0]);
				var enddate = EL.items[ELindex].data.enddate.split(".");
				enddate = new Date(enddate[2], enddate[1]-1, enddate[0]);

				if (startdate <= current && enddate >= current) {
					 cell.className += " MySched_CalendarEvent";
					 var len = cell.children[0].events.length;
					 cell.children[0].events[len] = EL.items[ELindex];
					 if (!cell.children[0].className.contains(" calendar_tooltip")) cell.children[0].className += " calendar_tooltip";
				}
			}
        };

        for(; i < me.numDays; ++i) {
            if (i < startingPos) {
                html = (++prevStart);
                cls = me.prevCls;
            } else if (i >= days) {
                html = (++extraDays);
                cls = me.nextCls;
            } else {
                html = i - startingPos + 1;
                cls = me.activeCls;
            }
            textNodes[i].innerHTML = html;
            cells[i].className = cls;
            current.setDate(current.getDate() + 1);
            setCellClass(cells[i]);
        }

        Ext.select('.calendar_tooltip', false, document).removeAllListeners();
		Ext.select('.calendar_tooltip', false, document).on({
			'mouseover': function (e) {
				e.stopEvent();
				calendar_tooltip(e);
			},
			'mouseout': function (e) {
				e.stopEvent();
				if (Ext.getCmp('mySched_calendar-tip')) Ext.getCmp('mySched_calendar-tip').destroy();
			},
			scope: this
		});

        me.monthBtn.setText(me.monthNames[date.getMonth()] + ' ' + date.getFullYear());
    }
});

calendar_tooltip = function (e) {
	var el = e.getTarget('.calendar_tooltip', 5, true);
	if (Ext.getCmp('mySched_calendar-tip')) Ext.getCmp('mySched_calendar-tip').destroy();
	var xy = el.getXY();
	xy[0] = xy[0] + el.getWidth() + 10;

	var events = el.dom.events;
	var htmltext = "";
	for (var i = 0; i < events.length; i++) {
		if (Ext.isObject(events[i])) {
			htmltext += events[i].data.title;
			var name = "";
			for(var obj in events[i].data.objects)
			{
				if(typeof obj != "function")
					if(name != "")
						name += ", ";
					if(obj.substring(0, 3) == "RM_") {
						name += MySched.Mapping.getName("room", obj);
					} else if(obj.substring(0, 3) == "TR_") {
						name += MySched.Mapping.getName("doz", obj);
					} else if(obj.substring(0, 3) == "CL_"){
						name += MySched.Mapping.getName("clas", obj);
					}
			}
			if(name != "")
				htmltext += " ("+ name + ")<br/>";
		}
		else {
			htmltext += events[i] + "<br/>";
		}
	}

	if (events.length > 0) {
		var ttInfo = Ext.create('Ext.tip.ToolTip', {
			title: '<div class="mySched_tooltip_calendar_title">Termin(e):</div>',
			id: 'mySched_calendar-tip',
			target: el.id,
			anchor: 'left',
			autoHide: false,
			html: htmltext,
			cls: "mySched_tooltip_calendar"
		});

		ttInfo.show(xy);
	}
}