clear;
$url = 'http://localhost:80/etaxware/updateErpInvoices';
$ApiKey = '2PaYBJn9SEm0RjvnrqD23oVYDULhJDHPDqxoZnol';
$response = '';
$payLoad = '{"VERSION":"5.0.0", "ERPUSER":"Admin", "WINDOWSUSER":"Admin", "IPADDRESS":"3.135.118.245", "MACADDRESS":"bc305bb5640a", "SYSTEMNAME":"ADMIN-PC", "APIKEY":"2PaYBJn9SEm0RjvnrqD23oVYDULhJDHPDqxoZnol"}';
$JSON = '';
$res = '';

$logpath = "$PSCommandPath.log" 
#Write-Output $logpath;

function Write-Log {
    [CmdletBinding()]
    Param(
    [Parameter(Mandatory=$False)]
    [ValidateSet("INFO","WARN","ERROR","FATAL","DEBUG")]
    [String]
    $Level = "INFO",

    [Parameter(Mandatory=$True)]
    [string]
    $Message,

    [Parameter(Mandatory=$False)]
    [string]
    $Logfile
    )

    $Stamp = (Get-Date).toString("yyyy/MM/dd HH:mm:ss")
    $Line = "$Stamp $Level $Message"
    
	if($Logfile) {
        Add-Content $Logfile -Value $Line
    }
    else {
        Write-Output $Line
    }
}

try{
    #$JSON = $payLoad | ConvertTo-Json -Compress
    $JSON = $payLoad
} catch {
    throw $_; 
    Exit;
}

try{
    #[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12;
    $response = Invoke-WebRequest -UseBasicParsing -Uri $url -Method POST -Body ($JSON) -ContentType 'application/json';
    if ($response) { 
        $res = $response.Content; 
    } else { 
        $res = 'The API returned NOTHING!'; 
    }
    
    #Write-Output $res;
    Write-Log -Level 'INFO' -Message $res  -Logfile $logpath
} catch {
    #Write-Output $_.ErrorDetails.Message.Content;
    Write-Log -Level 'INFO' -Message 'An internal error occured'  -Logfile $logpath
    Exit;
}
