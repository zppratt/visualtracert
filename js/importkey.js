var reader;

/**
 * Check for the various File API support.
 */
function checkFileAPI() {
    if (window.File && window.FileReader && window.FileList && window.Blob) {
        reader = new FileReader();
        return true;
    } else {
        alert('The File APIs are not fully supported by your browser. Fallback required.');
        return false;
    }
}

/**
 * read text input
 */
function readText(filePath) {
    var key = ""; //placeholder for text key
    if (filePath.files && filePath.files[0]) {
        reader.onload = function(e) {
            key = e.target.result;
            writeAPIKey(key);
        }; //end onload()
        reader.readLine(filePath.files[0]);
    } //end if html5 filelist support
    else if (ActiveXObject && filePath) { //fallback to IE 6-8 support via ActiveX
        try {
            reader = new ActiveXObject("Scripting.FileSystemObject");
            var file = reader.OpenTextFile(filePath, 1); //ActiveX File Object
            alert("Got the file.n" + "name: " + file.name + "n" + "type: " + file.type + "n" + "size: " + file.size + " bytesn" + "starts with: " + contents.substr(1, contents.indexOf("n")));
            key = file.ReadAll(); //text contents of file
            file.Close(); //close file "input stream"
            writeAPIKey(key);
        } catch (e) {
            if (e.number == -2146827859) {
                alert('Unable to access local files due to browser security settings. ' +
                    'To overcome this, go to Tools->Internet Options->Security->Custom Level. ' +
                    'Find the setting for "Initialize and script ActiveX controls not marked as safe" and change it to "Enable" or "Prompt"');
            }
        }
    } else {
        return false;
    }
    return true;
}

/**
 * display content using a basic HTML replacement
 */
function writeAPIKey(key) {
    document.getElementsById("map").setAttribute("src",
        "https://maps.googleapis.com/maps/api/js?key=" + key + "&signed_in=true&libraries=places&callback=initMap");
    el.innerHTML = key; //display output in DOM
}