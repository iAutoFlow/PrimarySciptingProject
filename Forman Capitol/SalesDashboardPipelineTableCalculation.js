/**
 * Created by Isaac on 10/23/2017.
 */


var table = "Deal Name | Assigned To | Pipeline Status | Deal Status | Loan Amount\n---|---|---|---|---:\n";

var pipelineJSON = @All of AVA JSON Field;

var newPipelineObj = [];

var assignedToFilter = @Assigned To Filter;

var monthFilter = @Month Filter;

var yearFilter = @Year Filter;

var pipelineStatusFilter = @Pipeline Status Filter;

var sortType = @Sort Type;

var tableSort = @Table Sort;

var textSearch = @Text Search;

var objCounter = 0;

try {

    for(p=0;p<pipelineJSON.length;p++){

        var pipelineObj = {};

        var assignedPass = true;
        var monthPass = true;
        var yearPass = true;
        var pipelineStatusPass = true;

        cleanPipelineAva = pipelineJSON[p].replace('![hello](http://www.techego.com/storage/Ava-Logo-Horizontal-100.png "AVA has made this field to better your calculation fields and overall system performance. Do not touch please.\n', '');

        cleanPipelineAva= cleanPipelineAva.slice(0, -2);


        pipelineObj = JSON.parse(cleanPipelineAva);


        if(assignedToFilter){
            var assignedPass = false;

            if((assignedToFilter.join()).indexOf(pipelineObj['custom']['assignedTo']) >= 0){
                var assignedPass = true;
            }

        }

        if(yearFilter){
            var yearPass = false;

            if((yearFilter.join()).indexOf(moment(pipelineObj['auto']['acquire-date']).format("YYYY")) >= 0){
                var yearPass = true;
            }

        }

        if(monthFilter){
            var monthPass = false;

            if((monthFilter.join()).indexOf(moment(pipelineObj['auto']['acquire-date']).format("MMMM")) >= 0 || (monthFilter.join()).indexOf("ALL") >= 0){
                var monthPass = true;
            }

        }

        if(pipelineStatusFilter){
            var pipelineStatusPass = false;

            for(s in pipelineStatusFilter){

                if(pipelineObj['auto']['pipeline-status'] == pipelineStatusFilter[s]){
                    var pipelineStatusPass = true;
                }

            }

        }

        if(assignedPass && monthPass && yearPass && pipelineStatusPass) {

            newPipelineObj[objCounter] = {
                "dealName": pipelineObj['auto']['deal-name']||"",
                "assignedTo": pipelineObj['custom']['assignedTo']||"",
                "pipelineStatus": pipelineObj['auto']['pipeline-status']||"",
                "dealStatus": pipelineObj['auto']['status']||"",
                "stepsToComplete": pipelineObj['auto']['action-2']||"",
                "loanAmount": pipelineObj['auto']['loan-request']||0,
                "date": moment(pipelineObj['auto']['acquire-date']).format("MM/DD/YYYY")||"",
                "sortDate": moment(pipelineObj['auto']['acquire-date']).format("YYYYMMDDHHmmss")||0,
                "uid": pipelineObj['auto']['unique-id']||"",
                "item-link": pipelineObj['auto']['item-link']||""
            };

            objCounter++;

        }

    }


    if(tableSort){

        if(tableSort == "Deal Name"){

            if(sortType == "Ascending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.dealName.toLowerCase(), nameB=b.dealName.toLowerCase();
                    if (nameA < nameB) //sort string ascending
                        return -1;
                    if (nameA > nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

            if(sortType == "Descending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.dealName.toLowerCase(), nameB=b.dealName.toLowerCase();
                    if (nameA > nameB) //sort string ascending
                        return -1;
                    if (nameA < nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

        }

        if(tableSort == "Assigned To"){

            if(sortType == "Ascending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.assignedTo.toLowerCase(), nameB=b.assignedTo.toLowerCase();
                    if (nameA < nameB) //sort string ascending
                        return -1;
                    if (nameA > nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

            if(sortType == "Descending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.assignedTo.toLowerCase(), nameB=b.assignedTo.toLowerCase();
                    if (nameA > nameB) //sort string ascending
                        return -1;
                    if (nameA < nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

        }

        if(tableSort == "Pipeline Status"){

            if(sortType == "Ascending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.pipelineStatus.toLowerCase(), nameB=b.pipelineStatus.toLowerCase();
                    if (nameA < nameB) //sort string ascending
                        return -1;
                    if (nameA > nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

            if(sortType == "Descending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.pipelineStatus.toLowerCase(), nameB=b.pipelineStatus.toLowerCase();
                    if (nameA > nameB) //sort string ascending
                        return -1;
                    if (nameA < nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

        }

        if(tableSort == "Deal Status"){

            if(sortType == "Ascending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.dealStatus.toLowerCase(), nameB=b.dealStatus.toLowerCase();
                    if (nameA < nameB) //sort string ascending
                        return -1;
                    if (nameA > nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

            if(sortType == "Descending"){
                newPipelineObj.sort(function(a, b){
                    var nameA=a.dealStatus.toLowerCase(), nameB=b.dealStatus.toLowerCase();
                    if (nameA > nameB) //sort string ascending
                        return -1;
                    if (nameA < nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });
            }

        }

        if(tableSort == "Loan Amount"){

            if(sortType == "Ascending"){
                newPipelineObj.sort(function(a, b){
                    return a.loanAmount-b.loanAmount
                })//end sort low to high
            }

            if(sortType == "Descending"){
                newPipelineObj.sort(function(a, b){
                    return b.loanAmount-a.loanAmount
                })//end sort low to high
            }

        }

        if(tableSort == "Date"){

            if(sortType == "Ascending"){
                newPipelineObj.sort(function(a, b){
                    return a.sortDate-b.sortDate
                })//end sort low to high
            }

            if(sortType == "Descending"){
                newPipelineObj.sort(function(a, b){
                    return b.sortDate-a.sortDate
                })//end sort low to high
            }

        }

    }


    for(i=0;i<newPipelineObj.length;i++){

        var row = "";

        row += "[" + newPipelineObj[i]['dealName'].slice(0, 20) + "](" + newPipelineObj[i]['item-link'] + " \""+newPipelineObj[i]['dealName']+"\") | " + newPipelineObj[i]['assignedTo'] + " | " + newPipelineObj[i]['pipelineStatus'] + " | " + newPipelineObj[i]['dealStatus'] + " | $" + newPipelineObj[i]['loanAmount'].toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + "\n";

        if(newPipelineObj[i]['stepsToComplete']){

            row += "**Steps to Complete:**";

            if(newPipelineObj[i]['stepsToComplete'].length > 4) {

                for (s = 0; s < 4; s++) {

                    row += " | *-" + newPipelineObj[i]['stepsToComplete'][s] + "*";

                }

            }else{

                for (s = 0; s < 4; s++) {

                    if(newPipelineObj[i]['stepsToComplete'][s]) {

                        row += " | *-" + newPipelineObj[i]['stepsToComplete'][s] + "*";

                    }else{

                        row += "|";

                    }

                }

            }

            if(newPipelineObj[i]['stepsToComplete'].length > 4){

                row += "\n";

                for(s2=4;s2<9;s2++){

                    if(newPipelineObj[i]['stepsToComplete'][s2]) {

                        row +=  "*-" + newPipelineObj[i]['stepsToComplete'][s2] + "*";

                    }else{

                        row += "|";

                    }

                    if(s2 !== 8){
                        row += " | ";
                    }

                }

            }

            row += "\n";

        }

        if(textSearch) {

            if (row.indexOf(textSearch) >= 0) {

                table += row;

            }

        }
        else{
            table += row;
        }

    }


    table;

}catch(e){

    "JSON Parse Issue: " + e;

}

// Dashboard Name 	text 	dashboardname 	152667045
// Year Filter 	category 	yearfilter 	152667047
// Month Filter 	category 	monthfilter 	152667046
// Pipeline Status Filter 	category 	pipelinestatusfilter 	152667048
// Assigned To Filter 	contact 	assignedtofilter 	152667049
// Sort Type 	category 	sorttype 	152667050
// Table Sort 	category 	table-sort 	152678492
// Text Search 	text 	textsearch 	152667052
// PipelineTable 	calculation 	pipelinetable 	152671514