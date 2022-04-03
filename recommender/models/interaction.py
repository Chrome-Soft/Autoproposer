import pandas as pd
from pypika import Query, Tables, JoinType
from model import Model

class Interaction(Model):
    def getBy(self, segment_id):
        i, ud, ii, p = Tables('interactions', 'user_data', 'interaction_items', 'products')
        q = Query.from_(i).join(
            ud, JoinType.left
        ).on(
            ud.cookie_id == i.cookie_id
        ).join(
            ii, JoinType.left
        ).on(
            ii.interaction_id == i.id
        ).join(
            p, JoinType.left
        ).on(
            p.id == ii.item_id
        ).where(
            ud.segment_id == str(segment_id)
        ).where(
            p.id.notnull()
        ).select(
            i.id,i.type,ii.item_id
        )

        data = super(Interaction, self).query(q);

        df = pd.DataFrame(data, columns=['id', 'type', 'product_id'])
        df['product_ids'] = pd.Series([[] for _ in range(len(df))])

        df = super(Interaction, self).merge_joined_rows(df, self.join_attribute_collector, self.join_parent_attribute_collector)

        return df.drop(columns=['product_id','id'])

    def join_attribute_collector(self, df, i, j):
        df.iloc[i]['product_ids'].append(df.iloc[j]['product_id'])

    def join_parent_attribute_collector(self, df, row, i):
        if row['product_id'] != None:
            row['product_ids'].append(row['product_id'])
