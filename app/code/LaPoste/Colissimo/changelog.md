# Colissimo Magento 2 Changelog

## 1.0.11

## Correctifs

- Correction de la mise à jour des status de livraison qui pouvait prendre beaucoup de ressources
- Sur le checkout, dans le cas d'une livraison en point relais, l'adresse de livraison pouvait être incorrecte


## 1.0.10

### Améliorations

- Ajout du paramètre FTD dans l'export ColiShip et dans le fichier d'exemple FMT
- Plus d'informations sont désormais ajoutées au fichier de log lors du debug

### Correctifs

- Correction d'un bug qui ne prenait pas en compte la virgule dans le tableau de définition des prix des méthodes de livraison


## 1.0.9

### Nouvelles fonctionnalités

- Impression en masse des étiquettes de livraison depuis le listing des expéditions Colissimo

### Améliorations

- Ajout de la référence de la commande sur l'étiquette de livraison
- Lors de l'impression d'une étiquette de livraison, la facture n'est plus présente
- Ajout du paramètre CUSER_INFO_TEXT dans l'export ColiShip et dans le fichier d'exemple FMT

### Corectifs

- Correction d'un bug d'afffichage de l'adresse de l'expéditeur sur les étiquettes de livraison si celle ci était sur deux lignes
