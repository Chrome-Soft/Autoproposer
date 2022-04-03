import numpy as np

def softmax(xs):
    xs_exp = [np.exp(x) for x in xs]
    sum_xs_exp = sum(xs_exp)
    softmax = [round(x / sum_xs_exp, 3) for x in xs_exp]

    return softmax;
