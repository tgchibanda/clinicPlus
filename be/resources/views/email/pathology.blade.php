<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
</head>
<style>
    #watermark {
        position: fixed;
        top: 45%;
        width: 100%;
        text-align: center;
        opacity: .3;

      }
    #footer-img {
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: right;

      }
      #footer {
        position: fixed;
        bottom: 60;
        width: 100%;
        font:12px  "Google Sans", Roboto, arial, sans-serif;
      }
    .header{
        font-size: 12px;
    }
    .floatedTable {
        float:left;
    }
    .general-width {
        width: 49.9%;
    }
    td {
        /* border-bottom: 1px solid #ddd; */
        margin: 5px;
    }
    body{
        font-family: "Google Sans", Roboto, arial, sans-serif;
        font:10px  "Google Sans", Roboto, arial, sans-serif;
    }
    * {
        box-sizing: border-box;
    }
    .container {
        min-height: 297mm;
    }
    .leave{
        padding-top: 150px;
    }
    footer {
        display: flex;
        justify-content: center;
        padding: 5px;
        /* background-color: #45a1ff; */
        /* color: #fff; */
    }
    .ytd-row {
        padding-top: 175px;
    }
    .column {
        float: left;
        width: 33.33%;
        padding-top: 2px;
        max-height: 200px;
        /* Should be removed. Only for demonstration */
    }
    /* Clear floats after the columns */
    
    .row:after {
        content: "";
        display: table;
        clear: both;
    }
    tr.border_bottom td {
        border-top:1pt solid black;
        border-bottom:1pt solid black;
      }
</style>

<body>
    {{--  watermark  --}}
    <div id="watermark">
        <img style="width: 30px;" height="20px" src="{{  storage_path('app/public/img/clinicPlus-logo.png') }}">
        {{ env('APP.APP_NAME') }}
    </div>

    {{--  Header  --}}
    <div class="container1">
        <div class="header">
            <div class="row">
                <div class="column">
                    <img style="width: 150px" height="130px" src="{{ storage_path('app/public/img/clinicPlus-logo.png')  }}">
                </div>
            </div>
        </div>
        {{--  container  --}}
        <div class="container">
            <table style="width: 100%">
                <tr>
                    <td style="width: 50%">
                        <b>Name : </b> {{ $details->fullname }} 
                    </td>
                    <td style="width: 50%">
                        <b>DOB :</b>  {{ $details->dob }} 
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <b>Address : </b> {{ $details->address_line1 }} 
                        {{ $details->address_line2 }}
                        {{ $details->address_line3 }}
                        {{ $details->city }}
                    </td>
                    <td style="width: 50%">
                        <b>Date :</b> {{ date('Y-m-d') }}
                    </td>
                </tr>

            </table>

            <img src="{{ storage_path('app/public/img/pathology.png') }}" style="width: 690px" height="540px"  >
        </div>
        <div id="footer">
            DR:  {{ $details->name }}  <br>                                                                                                       
            Signature: Electronic
        </div>
            {{--  Footer image  --}}
        <div id="footer-img">
            <img style="width: 90px" height="40px" src="{{ storage_path('app/public/img/clinicPlus-logo.png')  }}">
        </div>
    </div>

</body>

</html>