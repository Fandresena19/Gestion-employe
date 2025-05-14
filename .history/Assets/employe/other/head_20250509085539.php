<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../bootstrap4/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../bootstrap4/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="../css/style_nav.css">
  <link rel="stylesheet" href="../css/conge.css">
  <link rel="stylesheet" href="../css/modal.css">
  <title>Mes congés</title>

  <style>
    tbody tr:nth-child(even) {
      background-color: rgb(91, 91, 91);
    }
    .conge-summary {
      background-color: #6a6363bf;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .conge-summary h4 {
      color: #e0e0e0ce;
      margin-bottom: 10px;
    }
    .conge-detail {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
    }
    .conge-item {
      text-align: center;
      padding: 8px;
      margin: 5px;
      min-width: 150px;
      border-radius: 5px;
    }
    .conge-item.taken {
      background-color: #f0ad4e;
      max-height: 50px;
    }
    .conge-item.remaining {
      background-color: #5cb85c;
      max-height: 50px;
      color: white;
    }
    .conge-item.quota {
      background-color: #5bc0de;
      max-height: 50px;
      color: white;
    }
  </style>
</head>