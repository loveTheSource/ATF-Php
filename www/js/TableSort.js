// TableSort 10.0
// Jürgen Berkemeier, 28. 3. 2019
// www.j-berkemeier.de

(function() {
	
	"use strict";

	var JB_tableSort = function(tab,startsort) {
		var dieses = this;
		
		// Dokumentensprache ermitteln
		var doclang = document.documentElement.hasAttribute("lang") ? document.documentElement.getAttribute("lang") : "de";

		// Tabellenelemente ermitteln
		var thead = tab.tHead;
		if(thead) {
			var tr_in_thead = thead.querySelectorAll("tr.sortierbar");
			if(!tr_in_thead.length) tr_in_thead = thead.rows;
		}
		if(tr_in_thead) var tabletitel = tr_in_thead[0].cells;   
		if( !(tabletitel && tabletitel.length > 0) ) { console.error("Tabelle hat keinen thead und/oder keine THs."); return null; }
		var tbdy = tab.tBodies;
		if( !(tbdy) ) { console.error("Tabelle hat keinen tbody."); return null; }
		tbdy = tbdy[0];
		var tr = tbdy.rows;
		if( !(tr && tr.length > 0) ) { console.error("Tabelle hat keine Zeilen im tbody."); return null; }
		var nrows = tr.length;
		var ncols = tr[0].cells.length;

		// Einige Variablen
		var arr = [];
		var sorted = -1;
		var sortsymbols = [];
		var sortbuttons = [];
		var sorttype = [];
		var firstsort = [];
		var startsort_u = -1,startsort_d = -1;
		var savesort = tab.className.indexOf("savesort")>-1 && tab.id && tab.id.length>0 && localStorage;
		var minsort = -1;

		// Stylesheets für Button im TH
		if(!document.getElementById("JB_stylesheet_tableSort")) {
			var sortbuttonStyle = document.createElement('style'); 
			sortbuttonStyle.id = "JB_stylesheet_tableSort";
			var stylestring = '.sortbutton { width:100%; height:100%; border: none; background-color: transparent; font: inherit; color: inherit; text-align: inherit; padding: 0; cursor: pointer; } ';		
			stylestring += 'table.sortierbar thead th span.visually-hidden, table[sortable] thead th span.visually-hidden { position: absolute !important; clip: rect(1px, 1px, 1px, 1px) !important; padding: 0 !important; border: 0 !important; height: 1px !important; width: 1px !important; overflow: hidden !important; white-space: nowrap !important; } ';
			stylestring += '.sortsymbol::after { display: inline-block; letter-spacing: -.2em; margin-left:.1em; width: 1.8em; } ';
			stylestring += '.sortsymbol.sortedasc::after { content: "▲▽" } ';
			stylestring += '.sortsymbol.sorteddesc::after { content: "△▼" } ';
			stylestring += '.sortsymbol.unsorted::after { content: "△▽" } '	;
			stylestring += '.sortierbar caption span{ font-weight: normal; font-size: .8em; } ';
			sortbuttonStyle.innerText = stylestring;
			document.head.appendChild(sortbuttonStyle);
		}

		var initTableHead = function(col) { // Kopfzeile vorbereiten
			if(tabletitel[col].className.indexOf("sortier")==-1) {
				return false;
			}
			if(tabletitel[col].className.indexOf("sortierbar-")>-1) {
				firstsort[col] = "desc";
			}
			else if(tabletitel[col].className.indexOf("sortierbar")>-1) {
				firstsort[col] = "asc";
			}
			var hinweis = doclang=="de"?'Sortiere nach ':'Sort by ';
			var sortbutton = document.createElement("button");
			sortbutton.innerHTML = "<span class='visually-hidden'>" + hinweis + "</span>" + tabletitel[col].innerHTML;
			sortbutton.title = sortbutton.textContent;
			sortbutton.className = "sortbutton";
			sortbutton.type = "button";
			sortbuttons[col] = sortbutton;
			var sortsymbol = sortbutton;
			var symbolspan = sortbutton.querySelectorAll("span");
			if(symbolspan && symbolspan.length) {
				for(var i=0;i<symbolspan.length;i++) {
					if(!symbolspan[i].hasChildNodes()) { 
						sortsymbol = symbolspan[i];
						break;
					}
				}
			}
			sortsymbol.classList.add("sortsymbol");
			if(tabletitel[col].className.indexOf("vorsortiert-")>-1) {
				sortsymbol.classList.add("sorteddesc");
				sorted = col;
			}
			else if(tabletitel[col].className.indexOf("vorsortiert")>-1) {
				sortsymbol.classList.add("sortedasc");
				sorted = col;
			}
			else {
				sortsymbol.classList.add("unsorted");
			}
			sortsymbols[col] = sortsymbol;
			if(tabletitel[col].className.indexOf("sortiere-")>-1) {
				startsort_d = col;
			}
			else if(tabletitel[col].className.indexOf("sortiere")>-1) {
				startsort_u = col;
			}
			sortbutton.addEventListener("click",function() { dieses.tsort(col); },false);
			tabletitel[col].innerHTML = "<span class='visually-hidden'>" + tabletitel[col].innerHTML + "</span>";
			tabletitel[col].appendChild(sortbutton);
			return true;
		} // initTableHead

		var getData = function (ele, col) {
			var dmy,val;
			
			// Datum trimmen
			var trmdat = function() { 
				if(dmy[0]<10) dmy[0] = "0" + dmy[0];
				if(dmy[1]<10) dmy[1] = "0" + dmy[1];
				if(dmy[2]<10) dmy[2] = "200" + dmy[2];
				else if(dmy[2]<20) dmy[2] = "20" + dmy[2];
				else if(dmy[2]<99) dmy[2] = "19" + dmy[2];
				else if(dmy[2]>9999) dmy[2] = "9999";
			}
			
			// Tabellenfelder auslesen
			if (ele.getAttribute("data-sort_key")) 
				val = ele.getAttribute("data-sort_key");
			else if (ele.getAttribute("sort_key")) 
				val = ele.getAttribute("sort_key");
			else 
				val = ele.textContent;
				// val = ele.textContent.trim().replace(/\s+/g," ")

				// Tausendertrenner entfernen, und , durch . ersetzen
			var tval = val.replace(/\s|&nbsp;|&#160;|\u00A0|&#8239;|\u202f|&thinsp;|&#8201;|\u2009/g,"").replace(",", ".");
			
			// auf Zahl prüfen
			if (!isNaN(tval) && tval.search(/[0-9]/) != -1) return tval;                    
			
			// auf Datum/Zeit prüfen
			if(!val.search(/^\s*\d+\s*\.\s*\d+\s*\.\s*\d+\s+\d+:\d\d\:\d\d\s*$/)) {  
				var dp = val.search(":");
				dmy = val.substring(0,dp-2).split(".");
				dmy[3] = val.substring(dp-2,dp);
				dmy[4] = val.substring(dp+1,dp+3);
				dmy[5] = val.substring(dp+4,dp+6);
				for(var i=0;i<6;i++) dmy[i] = parseInt(dmy[i],10);
				trmdat();
				for(var i=3;i<6;i++) if(dmy[i]<10) dmy[i] = "0" + dmy[i];
				return (""+dmy[2]+dmy[1]+dmy[0]+"."+dmy[3]+dmy[4]+dmy[5]).replace(/ /g,"");
			}
			if(!val.search(/^\s*\d+\s*\.\s*\d+\s*\.\s*\d+\s+\d+:\d\d\s*$/)) {
				var dp = val.search(":");
				dmy = val.substring(0,dp-2).split(".");
				dmy[3] = val.substring(dp-2,dp);
				dmy[4] = val.substring(dp+1,dp+3);
				for(var i=0;i<5;i++) dmy[i] = parseInt(dmy[i],10);
				trmdat();
				for(var i=3;i<5;i++) if(dmy[i]<10) dmy[i] = "0"+dmy[i];
				return (""+dmy[2]+dmy[1]+dmy[0]+"."+dmy[3]+dmy[4]).replace(/ /g,"");
			}
			if(!val.search(/^\s*\d+:\d\d\:\d\d\s*$/)) {
				dmy = val.split(":");
				for(var i=0;i<3;i++) dmy[i] = parseInt(dmy[i],10);
				for(var i=0;i<3;i++) if(dmy[i]<10) dmy[i] = "0"+dmy[i];
				return (""+dmy[0]+dmy[1]+"."+dmy[2]).replace(/ /g,"");
			}
			if(!val.search(/^\s*\d+:\d\d\s*$/)) {
				dmy = val.split(":");
				for(var i=0;i<2;i++) dmy[i] = parseInt(dmy[i],10);
				for(var i=0;i<2;i++) if(dmy[i]<10) dmy[i] = "0"+dmy[i];
				return (""+dmy[0]+dmy[1]).replace(/ /g,"");
			}
			if(!val.search(/^\s*\d+\s*\.\s*\d+\s*\.\s*\d+/)) {
				dmy = val.split(".");
				for(var i=0;i<3;i++) dmy[i] = parseInt(dmy[i],10);
				trmdat();
				return (""+dmy[2]+dmy[1]+dmy[0]).replace(/ /g,"");
			}

			// String
			sorttype[col] = "s"; 
			return val;
		} // getData		

		var vglFkt_s = function(a,b) {
			var ret = a[sorted].localeCompare(b[sorted],doclang);
			if(!ret && sorted != minsort) {
				if(sorttype[minsort] == "s") ret = a[minsort].localeCompare(b[minsort],doclang);
				else                         ret = a[minsort] - b[minsort];
			}
			return ret;
		} // vglFkt_s

		var vglFkt_n = function(a,b) {
			var ret = a[sorted] - b[sorted];
			if(!ret && sorted != minsort) {
				if(sorttype[minsort] == "s") ret = a[minsort].localeCompare(b[minsort],doclang);
				else                         ret = a[minsort] - b[minsort];
			}
			return ret;
		} // vglFkt_n

		// Der Sortierer
		this.tsort = function(col) { 
			if(typeof(JB_presort)=="function") JB_presort(tab,tbdy,tr,nrows,ncols,col);

			if(col == sorted) { // Tabelle ist schon nach dieser Spalte sortiert, also nur Reihenfolge umdrehen
				arr.reverse();
				sortsymbols[col].classList.toggle("sortedasc"); 
				sortsymbols[col].classList.toggle("sorteddesc"); 
			}
			else {              // Sortieren 
				if(sorted>-1) {
					sortsymbols[sorted].classList.remove("sortedasc");
					sortsymbols[sorted].classList.remove("sorteddesc");
					sortsymbols[sorted].classList.add("unsorted");
					sortbuttons[sorted].removeAttribute("aria-current");
				}
				sorted = col;
				sortsymbols[col].classList.remove("unsorted");
				sortbuttons[col].setAttribute("aria-current","true");
				if(sorttype[col] == "n") arr.sort(vglFkt_n);
				else                     arr.sort(vglFkt_s);
				if(firstsort[col] == "desc") {
					arr.reverse();
					sortsymbols[col].classList.add("sorteddesc");
				}
				else {
					sortsymbols[col].classList.add("sortedasc");
				}
			}
			
			// Sortierte Daten zurückschreiben
			for(var r=0;r<nrows;r++) tbdy.appendChild(arr[r][ncols]); 

			// Aktuelle sortierung speichern
			if(savesort) {  
				var store = { sorted: sorted, desc: sortsymbols[sorted].className.indexOf("sorteddesc")>-1};
				localStorage.setItem(tab.id,JSON.stringify(store));
			}

			if(typeof(JB_aftersort)=="function") JB_aftersort(tab,tbdy,tr,nrows,ncols,col);
		} // tsort
		
		// Prüfen, ob kein tr im thead eine entsprechnde Klasse hat
		var sortflag = false;
		for(var c=0;c<tabletitel.length;c++) sortflag |= tabletitel[c].className.indexOf("sortier")>-1;
		if(!sortflag)	for(var c=0;c<tabletitel.length;c++) tabletitel[c].classList.add("sortierbar");
		
		// Kopfzeile vorbereiten
		for(var c=tabletitel.length-1;c>=0;c--) if(initTableHead(c)) minsort = c;
		
		// Array mit Info, wie Spalte zu sortieren ist, vorbelegen
		for(var c=0;c<ncols;c++) sorttype[c] = "n";
		
		// Tabelleninhalt in ein Array kopieren
		for(var r=0;r<nrows;r++) {
			arr[r] = [];
			for(var c=0;c<ncols;c++) {
				var cc = getData(tr[r].cells[c],c);
				arr[r][c] = cc ;
				// tr[r].cells[c].innerHTML += "<br>"+cc+"<br>"+sorttype[c]; // zum Debuggen
			}
			arr[r][ncols] = tr[r];
		}
		
		// Tabelle die Klasse "is_sortable" geben
		tab.classList.add("is_sortable");

		// An caption Hinweis anhängen
		var caption = tab.caption;
		if(caption) caption.innerHTML += doclang=="de"?
			"<br><span>Ein Klick auf die Spaltenüberschrift sortiert die Tabelle.</span>":
			"<br><span>A click on the column header sorts the table.</span>";
			
		// Bei Bedarf sortieren
		if(startsort && typeof(startsort.sorted)!="undefined" && typeof(startsort.desc)!="undefined") {
			if(startsort.desc) { startsort_d = startsort.sorted; startsort_u = -1; }
			else               { startsort_u = startsort.sorted; startsort_d = -1; }
		}
		if(startsort_u >= 0 && startsort_u < ncols) dieses.tsort(startsort_u); 
		if(startsort_d >= 0 && startsort_d < ncols) { dieses.tsort(startsort_d); dieses.tsort(startsort_d); }
		
		if(typeof(JB_aftersortinit)=="function") JB_aftersortinit(tab,tbdy,tr,nrows,ncols,-1);
	
	} // tableSort

	// Alle Tabellen suchen, die sortiert werden sollen, und den Tabellensortierer starten, wenn gewünscht, alte Sortierung wiederherstellen.
	if(window.addEventListener) window.addEventListener("DOMContentLoaded",function() { 
		var sort_Table = document.querySelectorAll("table.sortierbar, table[sortable]");
		for(var i=0,store;i<sort_Table.length;i++) {
			store = null;
			if(localStorage && sort_Table[i].className && sort_Table[i].id && sort_Table[i].className.indexOf("savesort")>-1 && sort_Table[i].id.length) {
				store = localStorage.getItem(sort_Table[i].id);
				if(store) {
					store = JSON.parse(store);
				}
			}
			new JB_tableSort(sort_Table[i],store);
		}
	},false); // initTableSort

})();  