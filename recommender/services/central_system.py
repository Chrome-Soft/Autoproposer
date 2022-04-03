import io
import requests
import pandas as pd

class CentralSystem:
    config = None

    def __init__(self, config):
        self.config = config

    def getUserData(self):
        url = self.config['host'] + '/api/user-data/csv'
        s = requests.get(url, headers={"X-Authorization": self.config['api_key']}).content
        return pd.read_csv(io.StringIO(s.decode('utf-8')))

    def getNextSequence(self):
        url = self.config['host'] + '/api/segments/sequence'
        s = requests.get(url, headers={"X-Authorization": self.config['api_key']}).content
        return int(s) + 1
