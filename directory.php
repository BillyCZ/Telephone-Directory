<html>
    <head>
        <meta charset="UTF-8">

        <style>
            body{
                font-family: arial, sans-serif;
            }

            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #dddddd;
                text-align: center;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>


        <title>Telephone directory</title>
    </head>
    <body>
    <center>
        <h1>Telephone directory</h1>
        <br><br>
        <p align="right">
            <a href="directory.txt">Show Source Code (TXT)</a><br>
            <a href="directory.php" download>Download Source Code (PHP)</a>
        </p>


        <form method="post">
            <button name="action" value="add">Add new Entry</button>
            <br><br>
            <button name="action" value="showall">Show all entries</button>
            <br><br>
            <button name="action" value="audit">Management Audit</button>
            <br><br>
        </form>
        <form method="post">
            <input name="search" size="30" maxlength='60' required>
            <button name="action" value="search" type="submit">Search</button>
        </form>
        <br><br>


        <?php
        // db connection

        $server = "127.0.0.1:3306";
        $username = "root";
        $password = "";
        $db = "telephone";

        /*
          $server = "md27.wedos.net";
          $username = "w196685_teldir";
          $password = "fAXv78gs";
          $db = "d196685_teldir";
         */

        $connect = new mysqli($server, $username, $password, $db);
        $connect->set_charset("utf8");


        // save actions
        if (isset($_POST['save'])) {

            //Add NEW Entry
            if ($_POST['save'] == 'AddNew') {
                $FirstName = $_POST['FirstName'];
                $Surname = $_POST['Surname'];
                $Phone = $_POST['Phone'];

                //add entry in audit
                $NewValue = $FirstName . ", " . $Surname . ", " . $Phone;
                $audit = "INSERT INTO audit (new_value, operation) VALUES ('$NewValue', 'ADD')";
                $connect->query($audit);

                //add entry in DB
                $add = "INSERT INTO directory (FirstName, Surname, Phone) VALUES ('$FirstName', '$Surname', '$Phone')";

                if ($connect->query($add) === TRUE) {
                    echo "Entry added succefully";
                } else {
                    echo "Error: " . $add . "<br>" . $connect->error;
                }

                // Edit existing entry
            } elseif (is_numeric($_POST['save']) === TRUE) {
                $ID = $_POST['save'];
                $FirstName = $_POST['FirstName'];
                $Surname = $_POST['Surname'];
                $Phone = $_POST['Phone'];

                //add entry in audit
                $Entry = $connect->query("SELECT * FROM directory WHERE ID = '$ID'");
                $OldValueEntry = mysqli_fetch_array($Entry);
                $OldValue = $OldValueEntry['FirstName'] . ", " . $OldValueEntry['Surname'] . ", " . $OldValueEntry['Phone'];
                $NewValue = $FirstName . ", " . $Surname . ", " . $Phone;
                $audit = "INSERT INTO audit (old_value, new_value, operation) VALUES ('$OldValue','$NewValue', 'EDIT')";
                $connect->query($audit);

                //save entry in DB
                $edit = "UPDATE directory SET FirstName = '$FirstName', Surname = '$Surname', Phone = '$Phone' WHERE ID = '$ID'";

                if ($connect->query($edit) === TRUE) {
                    echo "Entry updated succefully";
                } else {
                    echo "Error: " . $edit . "<br>" . $connect->error;
                }
            }
        }

        // edit entry form
        if (isset($_POST['edit'])) {

            $ID = $_POST['edit'];

            $Entry = $connect->query("SELECT * FROM directory WHERE ID = $ID");
            $ToEdit = mysqli_fetch_array($Entry);

            echo "<form method = 'post'><table><tr><th>ID</th><th>First Name</th><th>Surname</th><th>Phone Number</th><th>Save</th></tr>";

            echo "<tr><td><input size = 1 readonly name = 'ID' value = '" . $ToEdit['ID'] . "'";
            echo "</td><td><input name = 'FirstName' required maxlength=35 size=40 value = '" . $ToEdit['FirstName'] . "'";
            echo "</td><td><input name = 'Surname' required maxlength=35 size=40 value = '" . $ToEdit['Surname'] . "'";
            echo "</td><td><input name = 'Phone' required value = '" . $ToEdit['Phone'] . "'";
            echo "</td><td><button name = 'save' value = '" . $ToEdit['ID'] . "' > Save</button></td></tr>";

            echo "<tr><td></td><td>(Max. length: 35)</td><td>(Max. length: 35)";
            echo "</td></tr><tr><td colspan=5><font color='#ff0000'>All fields are required</font></td></tr></table></form>";
        }

        // Confirm delete
        if (isset($_POST['del'])) {
            $ID = $_POST['del'];
            $Entry = $connect->query("SELECT * FROM directory WHERE ID = '$ID'");
            $ToDelete = mysqli_fetch_array($Entry);
            echo "<font color='#ff0000'>Do you really want to delete this entry?</font><br><br>";
            echo "<table><tr><th>ID</th><th>First Name</th><th>Surname</th><th>Phone Number</th></tr>";
            echo "<tr><td>" . $ToDelete['ID'] . "</td><td>" . $ToDelete['FirstName'] . "</td><td>" . $ToDelete['Surname'] . "</td><td>" . $ToDelete['Phone'] . "</td></tr></table><br>";
            echo "<form method = 'post'><button name = 'del_confirmed' value = '$ID'>Yes</button>";
            echo "<button name = 'del_confirmed' value = '0' >No</button></form>";
        }

        // Delete confirmed entry
        if (isset($_POST['del_confirmed'])) {
            $ID = $_POST['del_confirmed'];

            If ($ID != 0) {

                //add entry in audit
                $Entry = $connect->query("SELECT * FROM directory WHERE ID = '$ID'");
                $OldValueEntry = mysqli_fetch_array($Entry);
                $OldValue = $OldValueEntry['FirstName'] . ", " . $OldValueEntry['Surname'] . ", " . $OldValueEntry['Phone'];
                $audit = "INSERT INTO audit (old_value, operation) VALUES ('$OldValue','DELETE')";
                $connect->query($audit);

                //delete entry in DB
                $del = "DELETE FROM directory WHERE ID='$ID'";
                if ($connect->query($del) === TRUE) {
                    echo "Entry deleted succefully";
                } else {
                    echo "Error: " . $del . "<br>" . $connect->error;
                }
            } else
                echo "Nothing happens";
        }


//main actions
        if (isset($_POST['action'])) {

            // search
            if ($_POST['action'] == "search") {

                $SearchFor = $_POST['search'];
                echo "<u>Searching for:" . $SearchFor . "</u><br><br>";

                $search_results = $connect->query("SELECT * FROM directory WHERE FirstName LIKE '%$SearchFor%' OR Surname LIKE '%$SearchFor%' OR Phone LIKE '%$SearchFor%' ORDER BY Surname DESC");


                echo "<table><tr><th>First Name</th><th>Surname</th><th>Phone Number</th><th>Edit</th><th>Delete</th></tr>";

                foreach ($search_results as $field) {
                    echo "<tr></td><td>" . $field['FirstName'];
                    echo "</td><td>" . $field['Surname'];
                    echo "</td><td>" . $field['Phone'];
                    echo "</td><form method = 'post'><td><button name = 'edit' value = '" . $field['ID'] . "' >Edit</button>";
                    echo "</td><td><button name = 'del' value = '" . $field['ID'] . "' >Delete</button>";
                }
                echo "</td></tr></table></form>";
            }


            // add new entry form
            if ($_POST['action'] == "add") {
                // Load last ID
                $LastID = $connect->query("SELECT ID FROM directory ORDER BY id DESC LIMIT 1")->fetch_object()->ID;

                echo "<form method = 'post'><table><tr><th>ID</th><th>First Name</th><th>Surname</th><th>Phone Number</th><th>Save</th></tr>";
                echo "<tr><td><input readonly name = 'ID' size=1 value=" . ($LastID + 1) . ">  </td>";
                echo "<td><input name = 'FirstName' required maxlength=35 size=40></td>";
                echo "<td><input name = 'Surname'  required maxlength=35 size=40></td>";
                echo "<td><input name = 'Phone'  required></td>";
                echo "<td><button name = 'save' value = 'AddNew'>Save</button></td></tr>";
                echo "<tr><td></td><td>(Max. length: 35)</td><td>(Max. length: 35)";
                echo "</td></tr><tr><td colspan=5><font color='#ff0000'>All fields are required</font></td></tr></table></form>";
            }



            // show all entries
            if ($_POST['action'] == "showall") {

                $directory = $connect->query("SELECT * FROM directory ORDER BY ID");
                echo "<table><tr><th>ID</th><th>First Name</th><th>Surname</th><th>Phone Number</th><th>Edit</th><th>Delete</th></tr>";
                foreach ($directory as $field) {
                    echo "<tr><td>" . $field['ID'];
                    echo "</td><td>" . $field['FirstName'];
                    echo "</td><td>" . $field['Surname'];
                    echo "</td><td>" . $field['Phone'];
                    echo "</td><form method = 'post'><td><button name = 'edit' value = '" . $field['ID'] . "' >Edit</button>";
                    echo "</td><td><button name = 'del' value = '" . $field['ID'] . "' >Delete</button>";
                }
                echo "</td></tr></table></form>";
            }


            // audit
            if ($_POST['action'] == "audit") {



                $audit_results = $connect->query("SELECT * FROM audit ORDER BY ID DESC");


                echo "<table><tr><th>ID</th><th>Old Value</th><th>New Value</th><th>Operation</th><th>Time stamp</th></tr>";

                foreach ($audit_results as $field) {
                    echo "<tr></td><td>" . $field['ID'];
                    echo "</td><td>" . $field['old_value'];
                    echo "</td><td>" . $field['new_value'];
                    echo "</td><td>" . $field['operation'];
                    echo "</td><td>" . $field['DateTime'];
                    echo "</td></tr>";
                }
                echo "</table>";
            }
        }
        ?>


        </body>
        </html>
