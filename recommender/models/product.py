# -*- coding: utf-8 -*-

import pandas as pd
from pypika import Query, Tables, JoinType
from model import Model

class Product(Model):
    def getAll(self):
        p, ppa, pa = Tables('products', 'product_product_attribute', 'product_attributes')
        q = Query.from_(p).join(
            ppa, JoinType.left
        ).on(
            ppa.product_id == p.id
        ).join(
            pa, JoinType.left
        ).on(
            pa.id == ppa.product_attribute_id
        ).select(p.id,p.name,p.name,pa.name,ppa.value)

        data = super(Product, self).query(q)
        df = pd.DataFrame(data, columns=['id','name','description','attribute','attribute_value'])
        df['attributes'] = pd.Series(["" for _ in range(len(df))])

        df = super(Product, self).merge_joined_rows(df, self.join_attribute_collector, self.join_parent_attribute_collector)
        return df.drop(columns=['attribute','attribute_value'])

    def get_most_popular(self):
        q = """
            select ii.item_id
            from interactions i
            left join interaction_items ii on ii.interaction_id = i.id
            left join products p on p.id = ii.item_id
            where p.id is not null
            group by ii.item_id
            order by sum(case
                when `type` = 'view' then 1
                else 2
            end) desc"""

        return super(Product, self).get_single_column(q);

    def join_attribute_collector(self, df, i, j):
        if isinstance(df.iloc[j]['attribute_value'], basestring):
            df.set_value(i,'attributes', u' '.join((df.iloc[j]['attribute'], df.iloc[j]['attribute_value'])).encode('utf-8', 'ignore'))
        else:
            df.set_value(i,'attributes',df.iloc[j]['attribute'] + " " + str(df.iloc[j]['attribute_value']))

    def join_parent_attribute_collector(self, df, row, i):
        if row['attribute'] != None:
            if isinstance(row['attribute_value'], basestring):
                try:
                    df.set_value(i,'attributes', u' '.join((row['attributes'], row['attribute'], row['attribute_value'])).encode('utf-8', 'ignore'))
                except Exception as error:
                    print error
            else:
                df.set_value(i,'attributes', row['attributes'] + " " + row['attribute'] + " " + str(row['attribute_value']))
