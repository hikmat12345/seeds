import people_also_ask
import sys

main_question = sys.argv[1]
answer = people_also_ask.get_simple_answer(main_question)

print (answer)