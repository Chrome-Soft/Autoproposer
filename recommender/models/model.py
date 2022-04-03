from mysql.connector import MySQLConnection, Error
from services.config import read_section

class Model(object):
    def query(self, query):
        conn = None
        cursor = None

        try:
            db_config = read_section('mysql')
            conn = MySQLConnection(**db_config)
            cursor = conn.cursor()

            if isinstance(query, basestring):
                cursor.execute(query)
            else:
                cursor.execute(query.get_sql(quote_char=None))

            return cursor.fetchall()

        except Error as error:
            print error

        finally:
            if cursor is not None and conn is not None:
                cursor.close()
                conn.close()

    def merge_joined_rows(self, df, attribute_collector_fn, parent_attribute_collector_fn):
        removable_indices = []
        for i,row in df.iterrows():
            j = i+1
            while j <= len(df) - 1 and df.iloc[j]['id'] == row['id']:
                attribute_collector_fn(df, i, j)
                removable_indices.append(j)
                j+=1

        df = df.drop(removable_indices)
        for i,row in df.iterrows():
            parent_attribute_collector_fn(df, row, i)

        return df

    def get_single_column(self, query):
        data = self.query(query)
        return [x[0] for x in data]
