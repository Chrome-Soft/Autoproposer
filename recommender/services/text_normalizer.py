# -*- coding: utf-8 -*-

import string
import re
import nltk

class TextNormalizer:
    def remove_punct(self, text):
        text_nopunct = "".join([char for char in text if char not in string.punctuation])
        return text_nopunct

    def remove_numbers(self, text):
        text_nonumbers = "".join([char for char in text if char not in ['0','1','2','3','4','5','6','7','8','9']])
        return text_nonumbers

    def tokenize(self, text):
        # W+ = A-Za-z0-9 vagy -
        tokens = re.split('\W+', text)
        return tokens

    def remove_stopwords(self, tokenized_list):
        stopwords = nltk.corpus.stopwords.words('hungarian')
        text = [word for word in tokenized_list if word not in stopwords]
        return text

    def stemming(self, tokenized_text):
        # ez a gyengen mukodo hun nlp
        stemmer = HungarianStemmer()
        text = [stemmer.stem(word) for word in tokenized_text]
        return text

    def lemmatizing(self, tokenized_text):
        wn = nltk.WordNetLemmatizer()
        text = [wn.lemmatize(word) for word in tokenized_text]
        return text

    def remove_accent(self, text):
        accent_map = {225: 'a', 233: 'e', 237: 'i', 243: 'o', 246: 'o', 337: 'o', 250: 'u', 369: 'u', 252: 'u'}
        new_text = ""

        for i,ch in enumerate(text):
            new_text += accent_map[ord(ch)] if ord(ch) in accent_map else ch

        return new_text

    def normalize(self, text):
        text = self.remove_punct(text)
        text = self.remove_numbers(text)
        text = self.remove_accent(text.lower())
        text = self.tokenize(text.lower())
        text = self.remove_stopwords(text)

        return self.lemmatizing(text)
