<?php
session_start();
session_destroy();
header("Location: ../project-vendas/index.html");
