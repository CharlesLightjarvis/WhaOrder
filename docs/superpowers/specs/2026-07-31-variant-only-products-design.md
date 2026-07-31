# Produits simples et produits à variantes

## Objectif

Permettre à un marchand de gérer deux formes de catalogue sans créer de données artificielles :

- un produit simple porte directement son prix, son stock et ses images ;
- un produit à variantes sert de fiche parente, tandis que chaque variante porte obligatoirement son prix et son stock, ainsi que ses propres images facultatives.

Un parent à variantes n'est jamais vendable directement. Par exemple, « Menschen » regroupe « A1.1 », « A1.2 », etc. La variante choisie détermine toujours le prix, le stock et les photos envoyées au client.

## Modèle de données

Les colonnes `products.price` et `products.stock` deviennent nullables.

- Produit simple : `products.price` et `products.stock` sont non nuls ; aucune variante n'existe.
- Produit à variantes : `products.price` et `products.stock` valent `NULL` ; au moins une variante existe.
- Variante : `product_variants.price` et `product_variants.stock` sont non nuls.

La présence de variantes détermine le mode du produit. Aucune colonne `type` supplémentaire n'est ajoutée.

La migration conserve les valeurs des produits existants. Les anciennes variantes dont le prix est nul doivent recevoir le prix du parent avant que leur colonne ne devienne non nullable. Leur stock existant est conservé. Après ce transfert, les parents possédant des variantes reçoivent `NULL` pour leur prix et leur stock.

## Règles d'écriture

Le formulaire expose un choix explicite « Produit simple » ou « Produit avec variantes ».

Pour un produit simple :

- le prix parent est obligatoire, numérique et positif ou nul ;
- le stock parent est obligatoire, entier et positif ou nul ;
- aucune variante n'est transmise.

Pour un produit à variantes :

- au moins une variante est obligatoire ;
- chaque variante exige un nom, un prix et un stock valides ;
- les champs prix et stock parents sont ignorés puis enregistrés à `NULL` ;
- les variantes ne peuvent plus hériter d'un prix parent.

Lors du passage de simple vers variantes, le marchand saisit les valeurs de toutes les variantes. Lors du passage de variantes vers simple, il saisit un nouveau prix et un nouveau stock parents ; les anciennes variantes sont supprimées selon le comportement actuel de mise à jour.

La validation est conditionnelle côté serveur. Le serveur normalise les données avant leur persistance afin qu'une requête modifiée manuellement ne puisse pas produire un état hybride.

## Liste des produits

Le backend charge, pour chaque produit :

- sa première image principale ;
- à défaut, la première image disponible parmi ses variantes ;
- le prix minimum et maximum des variantes ;
- la somme du stock des variantes ;
- le nombre de variantes.

Le DataTable affiche :

- Image : image principale, sinon première image de variante, sinon placeholder.
- Prix simple : prix du parent.
- Prix à variantes identiques : prix unique.
- Prix à variantes différents : fourchette `minimum – maximum`.
- Stock simple : stock du parent.
- Stock à variantes : somme suivie de « au total ».
- Variantes : nombre de variantes, comme actuellement.

Ces valeurs de présentation sont calculées par le backend et exposées dans `ProductResource`. Le frontend ne doit pas reconstruire les règles métier à partir d'une collection partielle.

## Commandes et agent WhatsApp

Tout flux qui calcule un prix ou vérifie un stock doit distinguer les deux modes.

- Pour un produit simple, le produit est directement sélectionnable.
- Pour un produit à variantes, une variante est obligatoire avant le calcul ou la finalisation d'une commande.
- Le prix unitaire et le stock proviennent toujours de la variante sélectionnée.
- La recherche produit présente la fourchette de prix et le stock total, puis invite à choisir une variante.
- L'agent ne doit jamais utiliser une valeur par défaut `0` lorsque le parent a un prix ou un stock nul.
- L'envoi de photos utilise les images de la variante choisie ; si une variante n'a pas d'image, il peut conserver le fallback actuel vers les images principales du produit.

Une tentative de commander un parent à variantes sans variante produit une erreur métier claire et récupérable par l'agent, afin qu'il demande le choix au client.

## Alertes de stock

Les alertes d'un produit simple utilisent le stock parent. Pour un produit à variantes, seules les alertes par variante sont pertinentes ; aucune alerte ne doit être calculée à partir du stock parent nul. Le stock total de la liste est uniquement informatif et ne remplace pas les seuils par variante.

## API et types frontend

`ProductResource` expose `price` et `stock` comme valeurs nullables. Il ajoute les valeurs de présentation stables `has_variants`, `cover_image`, `price_min`, `price_max` et `stock_total`. Les mêmes noms et nullabilités sont déclarés dans les types TypeScript.

Les pages d'édition continuent de recevoir toutes les images principales et toutes les images de variantes. Le fallback de couverture ne modifie ni ne duplique les associations d'images en base.

## Tests attendus

Les tests couvrent au minimum :

- création et modification d'un produit simple valide ;
- rejet d'un produit simple sans prix ou sans stock ;
- création et modification d'un produit à variantes avec parent nul ;
- rejet d'une variante sans prix ou sans stock ;
- migration d'une ancienne variante sans prix par héritage du prix parent ;
- couverture de liste depuis une image principale puis depuis une image de variante ;
- prix unique et fourchette de prix dans la ressource ;
- somme du stock des variantes ;
- calcul, modification et finalisation d'une commande simple ;
- obligation de sélectionner une variante pour un produit à variantes ;
- calcul et contrôle de stock fondés sur la variante ;
- recherche et présentation correctes par l'agent WhatsApp ;
- alertes de stock simples et par variante.

## Hors périmètre

- Plusieurs niveaux de variantes ou combinaisons d'attributs.
- Prix promotionnels, historiques de prix ou devises multiples.
- Réorganisation manuelle de l'image de couverture.
- Ajout d'un type de produit persistant en base.
