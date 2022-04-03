# -*- coding: utf-8 -*-

from __future__ import division
from sklearn.neighbors import NearestNeighbors
from fuzzywuzzy import fuzz
import pandas as pd
import numpy as np

class Recommender:
    text_normalizer = None

    def __init__(self, text_normalizer):
        self.text_normalizer = text_normalizer

    def recommend_batch(self, all_interactions, all_products_df, fallback_fn):
        recommended_ids = self.get_products_by_interactions(all_interactions)
        # next(iter()) returns the first elem. reommended_ids[0] throws IndexError if array is empty
        similar_ids = self.get_similar_product_ids(product_id=next(iter(recommended_ids or []), None),products=all_products_df)
        ids = self.add_similar_ids(recommended_ids, similar_ids)

        if not ids:
            ids = fallback_fn()

        if not ids:
            for i,row in all_products_df.iterrows():
                ids.append(row['id'])

        return ids

    def get_products_by_interactions(self, interactions_df):
        product_ids = []
        for i,inter in interactions_df.iterrows():
            for id in inter['product_ids']:
                product_ids.append(int(id))

        product_ids = list(set(product_ids))
        product_ids.sort(key = lambda x:self.get_score_by_interactions(x, interactions_df), reverse=True)

        return product_ids

    def get_similar_product_ids(self, product_id, products):
        if product_id == None:
            return []

        self.add_keywords(products)
        normalized_products = self.get_normalized_data(products)

        X = normalized_products.as_matrix()
        # Az osszes termekbol csak az elso N termeket fogja igy vizsgalni
        nbrs = NearestNeighbors(n_neighbors=len(products), algorithm='auto', metric='euclidean').fit(X)
        xtest = None

        for i,r in normalized_products.iterrows():
            if r['id'] == product_id: xtest = r

        xtest = xtest.as_matrix()
        xtest = xtest.reshape(1, -1)

        # distances: mennyire van "tavol" egy termek a keresett termektol, minel kisebb egy ertek, annal jobban hasonlitanak
        # indices: a termekek indexei
        # a 0 index mindig a keresett termek (hiszen az hasonlit legjobban sajat magara)
        # FIXME ez ugy nez ki, hogy szimplan visszaadja a products -bol az elso X elemet
        distances, indices = nbrs.kneighbors(xtest)
        distances = distances[0][1:]

        product = None
        for i,r in products.iterrows():
            if r['id'] == product_id: product = r

        neighbors_tmp = products.iloc[indices[0][1:]]
        neighbors = neighbors_tmp.values.tolist()
        for i, val in enumerate(neighbors):
            neighbors[i].append(distances[i])

        neighbors.sort(key=lambda x:self.get_score_by_similarity(product[1], x[1], x[-1]))

        return list(map(lambda x:x[0],neighbors))

    def add_similar_ids(self, recommended_ids, similar_ids):
        similar_ids_unique = [id for id in similar_ids if id not in recommended_ids]
        recommended_ids += similar_ids_unique

        return recommended_ids

    def add_custom_products(self, recommended_ids, custom_products, N):
        for custom_product in custom_products['always']:
            recommended_ids.insert(0, int(custom_product['id']))

        # Ezt kesobb pontszamozni kell
        if len(recommended_ids) < N:
            optional_ids = list(map(lambda x:x['id'],custom_products['optional']))
            recommended_ids += [id for id in optional_ids if id not in recommended_ids]

        return recommended_ids

    def get_score_by_interactions(self, product_id, interactions_df):
        product_info = {'buy':0, 'view':0, 'present':0}

        for i,inter in interactions_df.iterrows():
            for id in inter['product_ids']:
                if int(id) == int(product_id): product_info[inter['type']] += 1

        sum_interactions = len(interactions_df)
        score = product_info['view'] / sum_interactions
        score += (product_info['buy'] / sum_interactions)*2

        return score

    def get_score_by_similarity(self, main_name, name, distance):
        if fuzz.ratio(main_name,name) > 80 or fuzz.token_set_ratio(main_name,name) > 80:
            name_factor = distance
        elif fuzz.ratio(main_name,name) > 50 or fuzz.token_set_ratio(main_name,name) > 50:
            name_factor = 2
        elif fuzz.token_set_ratio(main_name,name) > 40:
            name_factor = 1.75
        else:
            name_factor = 1

        return distance / name_factor

    def add_keywords(self, products):
        for i,row in products.iterrows():
            try:
                products.set_value(i, 'keywords', u' '.join((row['name'], row['description'], row['attributes'])).encode('utf-8'))
            except Exception as error:
                products.set_value(i, 'keywords', u' '.join((str(row['name']), str(row['description']))).encode('utf-8'))
                print error

    def get_normalized_data(self, products):
        all_keywords = []
        for keywords in products['keywords']:
            for word in keywords:
                all_keywords.append(word.lower())

        variables = list(set(all_keywords))
        variables = [v for v in variables if v not in ['id', 'name', 'description']]

        new_dataset = products
        for v in variables: new_dataset[v] = pd.Series([0 for _ in range(len(new_dataset))])
        columns = ['name', 'description', 'keywords', 'attributes']

        for c in columns:
            for i,row in new_dataset.iterrows():
                if c == 'keywords':
                    for keyword in row[c]:
                        if keyword in variables: new_dataset.set_value(i, keyword, 1)
                else:
                    if pd.isnull(row[c]): continue
                    if row[c] in variables: new_dataset.set_value(i, row[c], 1)

        # Replace possilbe NaN values
        for i,row in new_dataset.iterrows():
            for col in new_dataset.columns:
                if col == 'id': continue
                if row[col] != 1 and row[col] != 0:
                    new_dataset.set_value(i, col, 0)

        removable = columns
        return new_dataset.drop(columns=removable)
