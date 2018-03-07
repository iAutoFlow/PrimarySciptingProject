try {

    for(a=0;a<avaJson.length;a++) {

        cleanAva = avaJson[a].replace('![hello](http://www.techego.com/storage/Ava-Logo-Horizontal-100.png "AVA has made this field to better your calculation fields and overall system performance. Do not touch please.\n', '');

        cleanAva = cleanAva.slice(0, -2);

        //tableObject += cleanAva;

        var json = JSON.parse(cleanAva);

        // tableObject += ']';




//Filter One-Off Activity

        if(
            (
                (
                    moment(json['custom']['endDate']).format("YYYY") == yearFilter && json['custom']['endDate'] != "Not Set"
                    &&
                    json['custom']['recognized'] === "One-Off Activity"
                    &&
                    (
                        monthFilter === "All"
                        ||
                        monthFilter === moment(json['custom']['endDate']).format("MMMM")
                        ||
                        monthFilter === "Q" + moment(json['custom']['endDate']).format("Q")
                    )
                )
                ||


                    //Filter One-Off Activity (Start Date)

                (
                    moment(json['custom']['startDate']).format("YYYY") == yearFilter && json['custom']['startDate'] != "Not Set"
                    &&
                    json['custom']['recognized'] === "One-Off Activity (Start Date)"
                    &&
                    (
                        monthFilter === "All"
                        ||
                        monthFilter === moment(json['custom']['startDate']).format("MMMM")
                        ||
                        monthFilter === "Q" + moment(json['custom']['startDate']).format("Q")
                    )
                )
                ||


                    //Filter Annual Plan

                (
                    (
                        moment(json['custom']['startDate']).format("YYYY") == yearFilter && json['custom']['startDate'] != "Not Set"
                        ||
                        moment(json['custom']['endDate']).format("YYYY") == yearFilter && json['custom']['endDate'] != "Not Set"
                    )
                    &&
                    json['custom']['recognized'] === "Annual Plan"
                )
                ||


                    //Filter Amortized One-Off

                (
                    (
                        moment(json['custom']['startDate']).format("YYYY") == yearFilter && json['custom']['startDate'] != "Not Set"
                        ||
                        moment(json['custom']['endDate']).format("YYYY") == yearFilter && json['custom']['endDate'] != "Not Set"
                    )
                    &&
                    json['custom']['recognized'] === "Amortized One-Off"
                )
            )
            && (json['custom']['sku-type'] == skuType || skuType == "All")
            && (json['custom']['recognized'] == recogFilter || skuType == "All")
            && (json['custom']['clientName'].indexOf(clientFilter) || !clientFilter)
            && (json['custom']['AM'].indexOf(accountManager) || !accountManager)
            && (json['custom']['GAM'].indexOf(groupManager) || !groupManager)
            //&& (json['custom']['BU'].indexOf(businessUnit) || !businessUnit)
            && (json['custom']['division'].indexOf(division) || !division)
        ){
            filterObject.push(json);
        }


    }


    for(i=0;i<columnFilterArray.length;i++){
        if(i=0){
            dashboard += columnFilterArray[i];
        }
        else{
            dashboard += " | " + columnFilterArray[i];
        }
    }

    for(i=0;i<columnFilterArray.length;i++){
        if(i=0){
            dashboard += "---";
        }
        else{
            dashboard += " | ---";
        }
    }



    for(var j in filterObject){

        uniqueClientArray.push(filterObject[j]['custom']['clientName']);

        filterObject[j]['custom'].monthsInYear = [];

        filterObject[j]['custom'].planned = 0;

        filterObject[j]['custom'].grandTotal = 0;

        filterObject[j]['custom'].plannedProfit = 0;

        filterObject[j]['custom'].profit = 0;

        filterObject[j]['custom'].endPM = 0;


        if(moment(filterObject[j]['custom']['endDate']).format("YYYY") > moment(filterObject[j]['custom']['startDate']).format("YYYY") && moment(filterObject[j]['custom']['endDate']).format("YYYY") === yearFilter){

            for(m=1;m<=moment(filterObject[j]['custom']['endDate']).format("M");m++){

                filterObject[j]['custom'].monthsInYear.push(m)

            }//end for m

        }//end if endyear > startyear, end match
        else if(moment(filterObject[j]['custom'].endDate).format("YYYY") > moment(filterObject[j]['custom'].startDate).format("YYYY") && moment(filterObject[j]['custom'].startDate).format("YYYY") == yearFilter){

            for(m=12;m>=moment(filterObject[j]['custom'].startDate).format("M");m--){

                filterObject[j]['custom'].monthsInYear.push(m)

            }//end for m

        }//end else endyear > startyear, start match
        else{

            var months = 1 + moment(filterObject[j]['custom'].endDate).diff(moment(filterObject[j]['custom'].startDate), 'months')

            //if(moment(filterObject[j].endDate).format("MM/DD") == "12/31"){months += 1}

            if(months > 0){

                for(m=0;m<months;m++){

                    filterObject[j]['custom'].monthsInYear.push(moment(filterObject[j]['custom'].endDate).format("M") - m)

                }//end for m

            }//end if more than 1 month
            else{

                filterObject[j]['custom'].monthsInYear.push(moment(filterObject[j]['custom'].endDate).format("M"))

            }//end else push end month

        }//end else year greater check

        var miy = filterObject[j]['custom'].monthsInYear;

        if(filterObject[j]['custom'].recognized === "Amortized One-Off" || filterObject[j]['custom'].recognized === "Annual Plan"){

            if(monthFilter === "All" || monthFilter === "Q1" || monthFilter === "January") {

                if (miy.indexOf(1) > -1 || miy == 1) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-january'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q1" || monthFilter === "February") {
                if (miy.indexOf(2) > -1 || miy == 2) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-february'] / 1;

                }
            }
            if(monthFilter === "All" || monthFilter === "Q1" || monthFilter === "March") {
                if (miy.indexOf(3) > -1 || miy == 3) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-march'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q2" || monthFilter === "April") {
                if (miy.indexOf(4) > -1 || miy == 4) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-april'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q2" || monthFilter === "May") {
                if (miy.indexOf(5) > -1 || miy == 5) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-may'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q2" || monthFilter === "June") {
                if (miy.indexOf(6) > -1 || miy == 6) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-june'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q3" || monthFilter === "July") {
                if (miy.indexOf(7) > -1 || miy == 7) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-july'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q3" || monthFilter === "August") {
                if (miy.indexOf(8) > -1 || miy == 8) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-august'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q3" || monthFilter === "September") {
                if (miy.indexOf(9) > -1 || miy == 9) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-september'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q4" || monthFilter === "October") {
                if (miy.indexOf(10) > -1 || miy == 10) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-october'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q4" || monthFilter === "November") {
                if (miy.indexOf(11) > -1 || miy == 11) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-november'] / 1;
                }
            }
            if(monthFilter === "All" || monthFilter === "Q4" || monthFilter === "December") {
                if (miy.indexOf(12) > -1 || miy == 12) {
                    filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-december'] / 1;
                }
            }

        }
        else{

            if(monthFilter === "All" || monthFilter === "Q1" || monthFilter === "January") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-january'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q1" || monthFilter === "February") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-february'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q1" || monthFilter === "March") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-march'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q2" || monthFilter === "April") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-april'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q2" || monthFilter === "May") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-may'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q2" || monthFilter === "June") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-june'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q3" || monthFilter === "July") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-july'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q3" || monthFilter === "August") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-august'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q3" || monthFilter === "September") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-september'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q4" || monthFilter === "October") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-october'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q4" || monthFilter === "November") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-november'] / 1;
            }
            if(monthFilter === "All" || monthFilter === "Q4" || monthFilter === "December") {
                filterObject[j]['custom'].grandTotal += filterObject[j]['custom']['new-december'] / 1;
            }

        }//end else one-off



        if(monthFilter === "All") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['total-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['total-pm'];
            filterObject[j]['custom'].plannedProfit += ((filterObject[j]['custom']['planCJan'] + filterObject[j]['custom']['planCFeb'] + filterObject[j]['custom']['planCMar'] + filterObject[j]['custom']['planCApr'] + filterObject[j]['custom']['planCMay'] + filterObject[j]['custom']['planCJun'] + filterObject[j]['custom']['planCJul'] + filterObject[j]['custom']['planCAug'] + filterObject[j]['custom']['planCSep'] + filterObject[j]['custom']['planCOct'] + filterObject[j]['custom']['planCNov'] + filterObject[j]['custom']['planCDec']) * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCJan'] + filterObject[j]['custom']['planCFeb'] + filterObject[j]['custom']['planCMar'] + filterObject[j]['custom']['planCApr'] + filterObject[j]['custom']['planCMay'] + filterObject[j]['custom']['planCJun'] + filterObject[j]['custom']['planCJul'] + filterObject[j]['custom']['planCAug'] + filterObject[j]['custom']['planCSep'] + filterObject[j]['custom']['planCOct'] + filterObject[j]['custom']['planCNov'] + filterObject[j]['custom']['planCDec'];
        }
        if(monthFilter === "Q1") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['q1-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['q1-pm'];
            filterObject[j]['custom'].plannedProfit += ((filterObject[j]['custom']['planCJan'] + filterObject[j]['custom']['planCFeb'] + filterObject[j]['custom']['planCMar']) * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCJan'] + filterObject[j]['custom']['planCFeb'] + filterObject[j]['custom']['planCMar'];
        }
        if(monthFilter === "Q2") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['q2-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['q2-pm'];
            filterObject[j]['custom'].plannedProfit += ((filterObject[j]['custom']['planCApr'] + filterObject[j]['custom']['planCMay'] + filterObject[j]['custom']['planCJun']) * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCApr'] + filterObject[j]['custom']['planCMay'] + filterObject[j]['custom']['planCJun'];
        }
        if(monthFilter === "Q3") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['q3-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['q3-pm'];
            filterObject[j]['custom'].plannedProfit += ((filterObject[j]['custom']['planCJul'] + filterObject[j]['custom']['planCAug'] + filterObject[j]['custom']['planCSep']) * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCJul'] + filterObject[j]['custom']['planCAug'] + filterObject[j]['custom']['planCSep'];
        }
        if(monthFilter === "Q4") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['q4-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['q4-pm'];
            filterObject[j]['custom'].plannedProfit += ((filterObject[j]['custom']['planCOct'] + filterObject[j]['custom']['planCNov'] + filterObject[j]['custom']['planCDec']) * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCOct'] + filterObject[j]['custom']['planCNov'] + filterObject[j]['custom']['planCDec'];
        }
        if(monthFilter === "January") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['jan-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['jan-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCJan'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCJan'];
        }
        if(monthFilter === "February") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['feb-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['feb-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCFeb'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCFeb'];
        }
        if(monthFilter === "March") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['mar-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['mar-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCMar'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCMar'];
        }
        if(monthFilter === "April") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['apr-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['apr-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCApr'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCApr'];
        }
        if(monthFilter === "May") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['may-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['may-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCMay'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCMay'];
        }
        if(monthFilter === "June") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['jun-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['jun-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCJun'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCJun'];
        }
        if(monthFilter === "July") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['jul-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['jul-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCJul'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCJul'];
        }
        if(monthFilter === "August") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['aug-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['aug-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCAug'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCAug'];
        }
        if(monthFilter === "September") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['sep-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['sep-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCSep'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCSep'];
        }
        if(monthFilter === "October") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['oct-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['oct-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCOct'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCOct'];
        }
        if(monthFilter === "November") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['nov-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['nov-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCNov'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCNov'];
        }
        if(monthFilter === "December") {
            filterObject[j]['custom'].profit += (filterObject[j]['custom']['dec-pm'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].endPM = filterObject[j]['custom']['dec-pm'];
            filterObject[j]['custom'].plannedProfit += (filterObject[j]['custom']['planCDec'] * filterObject[j]['custom'].grandTotal)/1;
            filterObject[j]['custom'].planned += filterObject[j]['custom']['planCDec'];
        }
    }//end grand total


    var uniqueClient = uniqueClientArray.filter( onlyUnique );

    var clientObject = [];

    for(var w in uniqueClient){

        clientObject[w] = {clientName: uniqueClient[w],plannedRev: 0,revenue: 0,plannedProfit: 0,profit: 0}

    }


    for(var w in clientObject){

        if(clientFilter.length == 0 || clientFilter.indexOf(clientObject[w].clientName) >= 0){

            for(var j in filterObject){

                if(clientObject[w].clientName === filterObject[j]['custom']['clientName']){

                    clientObject[w].plannedRev = filterObject[j]['custom'].planned;

                    clientObject[w].revenue += filterObject[j]['custom'].grandTotal;

                    clientObject[w].plannedProfit = filterObject[j]['custom'].plannedProfit;

                    clientObject[w].profit += filterObject[j]['custom'].profit;

                    clientObject[w].endPM = filterObject[j]['custom'].endPM * 100;

                    clientObject[w].ofs = filterObject[j]['custom'].ofs;

                }//end if Client matches Unique

            }//end for j

            totalPlanned += clientObject[w].plannedRev/1;

            totalPlannedProfit += clientObject[w].plannedProfit/1;

            totalRevenue += clientObject[w].revenue/1;

            totalProfit += clientObject[w].profit/1;

        }//end if match

    }//end build end array


    for(var w in clientObject){

        if(clientSort.length == 0 || clientSort.indexOf(clientObject[w].clientName) >= 0){

//Start Sorting

            if(sortOpt == "Client: A to Z"){

                clientObject.sort(function(a, b){
                    var nameA=a.clientName.toLowerCase(), nameB=b.clientName.toLowerCase();
                    if (nameA < nameB) //sort string ascending
                        return -1;
                    if (nameA > nameB)
                        return 1;
                    return 0; //default return value (no sorting)
                });

            }//end name


            if(sortOpt == "Revenue: Low to High"){

                clientObject.sort(function(a,b){
                    return a.revenue-b.revenue
                })//end sort function

            }//end low-high rev


            if(sortOpt == "Revenue: High to Low"){

                clientObject.sort(function(a,b){
                    return b.revenue-a.revenue
                })//end sort function

            }//end high-low rev


//End Sorting

            dashboard += clientObject[w].clientName + " | ";

            for(i=0;i<columnFilterArray.length;i++){

                if(i!=0){
                    dashboard += " | ";
                }

                if(columnFilterArray[i] == "Planned Revenue"){
                    dashboard += "$"+clientObject[w].plannedRev.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
                if(columnFilterArray[i] == "Contracted Revenue"){
                    dashboard += "$"+clientObject[w].revenue.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
                if(columnFilterArray[i] == "Revenue Variance"){
                    dashboard += "$"+(clientObject[w].revenue - clientObject[w].plannedRev).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
                if(columnFilterArray[i] == "Planned Profit"){
                    dashboard += "$"+clientObject[w].plannedProfit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
                if(columnFilterArray[i] == "Contracted Profit"){
                    dashboard += "$"+clientObject[w].profit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
                if(columnFilterArray[i] == "Profit Variance"){
                    dashboard += "$"+(clientObject[w].profit - clientObject[w].plannedProfit).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
            }


            dashboard += "\n";

        }//end if Client match

    }//end for w build dash

    dashboard += "**Totals** | **";

    for(i=0;i<columnFilterArray.length;i++){

        if(i!=0){
            dashboard += "** | **";
        }

        if(columnFilterArray[i] == "Planned Revenue"){
            dashboard += "$"+totalPlanned.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        }
        if(columnFilterArray[i] == "Contracted Revenue"){
            dashboard += "$"+totalRevenue.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        }
        if(columnFilterArray[i] == "Revenue Variance"){
            dashboard += "$"+(totalRevenue - totalPlanned).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        }
        if(columnFilterArray[i] == "Planned Profit"){
            dashboard += "$"+totalPlannedProfit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        }
        if(columnFilterArray[i] == "Contracted Profit"){
            dashboard += "$"+totalProfit.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');;
        }
        if(columnFilterArray[i] == "Profit Variance"){
            dashboard += "$"+(totalProfit - totalProfit).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        }
    }

    dashboard += "**";

    var table = dashboard;

    write = table;



}catch(e){

    write = "Issue: " + e;

}