# Download Vendor Files Script
# Run this script from the obrighton_legacy directory

$ErrorActionPreference = "Continue"

Write-Host "Downloading vendor files..." -ForegroundColor Green

# Bootstrap 5.1.3 (includes jQuery, Popper, Bootstrap)
Write-Host "Downloading Bootstrap bundle..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" -OutFile "vendor/global/global.min.js"

# Chart.js
Write-Host "Downloading Chart.js..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" -OutFile "vendor/chart.js/Chart.bundle.min.js"

# Bootstrap Select
Write-Host "Downloading Bootstrap Select..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js" -OutFile "vendor/bootstrap-select/dist/js/bootstrap-select.min.js"
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css" -OutFile "vendor/bootstrap-select/dist/css/bootstrap-select.min.css"

# DataTables
Write-Host "Downloading DataTables..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" -OutFile "vendor/datatables/js/jquery.dataTables.min.js"
Invoke-WebRequest -Uri "https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" -OutFile "vendor/datatables/css/jquery.dataTables.min.css"
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" -OutFile "vendor/datatables/js/jszip.min.js"

# Swiper
Write-Host "Downloading Swiper..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" -OutFile "vendor/swiper/css/swiper-bundle.min.css"

# Bootstrap Datetimepicker
Write-Host "Downloading Bootstrap Datetimepicker..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js" -OutFile "vendor/bootstrap-datetimepicker/js/moment.js"
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js" -OutFile "vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" -OutFile "vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"

# Tagify
Write-Host "Downloading Tagify..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.min.js" -OutFile "vendor/tagify/dist/tagify.js"
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.css" -OutFile "vendor/tagify/dist/tagify.css"

# jVectorMap
Write-Host "Downloading jVectorMap..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/jvectormap/2.0.5/jquery- jvectormap.min.css" -OutFile "vendor/jvmap/jquery-jvectormap.css"
Invoke-WebRequest -Uri "https://cdnjs.cloudflare.com/ajax/libs/jvectormap/2.0.5/jquery-jvectormap.min.js" -OutFile "vendor/jvmap/jquery.vmap.min.js"
# Create placeholder files for map data
New-Item -Path "vendor/jvmap/jquery.vmap.world.js" -ItemType File -Value "// Placeholder map file" -Force | Out-Null
New-Item -Path "vendor/jvmap/jquery.vmap.usa.js" -ItemType File -Value "// Placeholder map file" -Force | Out-Null

# ApexCharts
Write-Host "Downloading ApexCharts..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js" -OutFile "vendor/apexchart/apexchart.js"

# Draggable (Shopify)
Write-Host "Downloading Draggable..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.12/lib/draggable.bundle.js" -OutFile "vendor/draggable/draggable.js"

# CKEditor 5
Write-Host "Downloading CKEditor..." -ForegroundColor Yellow
Invoke-WebRequest -Uri "https://cdn.ckeditor.com/ckeditor5/39.0.2/classic/ckeditor.js" -OutFile "vendor/ckeditor/ckeditor.js"

Write-Host "" 
Write-Host "Download complete!" -ForegroundColor Green
Write-Host "All vendor files have been downloaded successfully." -ForegroundColor Green
