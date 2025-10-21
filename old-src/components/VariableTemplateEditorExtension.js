import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';

export const VariableNode = Extension.create({
  name: 'variableNode',
  
  addOptions() {
    return {
      HTMLAttributes: {},
      variableRegex: /\{\{([A-Z_]+)\}\}/g,
    };
  },
  
  parseHTML() {
    return [
      {
        tag: 'span.variable-node',
      },
    ];
  },
  
  renderHTML({ HTMLAttributes }) {
    return ['span', { class: 'variable-node', ...HTMLAttributes }, 0];
  },
  
  addProseMirror() {
    return [
      new Plugin({
        key: new PluginKey('variableHighlighter'),
        props: {
          decorations(state) {
            const { doc } = state;
            const decorations = [];
            
            // Apply regex to find variables in the document
            const regex = this.options.variableRegex;
            
            doc.descendants((node, pos) => {
              if (node.isText) {
                const text = node.text;
                let match;
                
                // Reset the regex
                regex.lastIndex = 0;
                
                // Find all matches
                while ((match = regex.exec(text)) !== null) {
                  const from = pos + match.index;
                  const to = from + match[0].length;
                  
                  // Create a decoration for this match
                  const variableType = determineVariableType(match[1]);
                  
                  decorations.push(
                    Decoration.inline(from, to, {
                      class: `variable-node ${variableType}`,
                    })
                  );
                }
              }
            });
            
            return DecorationSet.create(doc, decorations);
          },
        },
      }),
    ];
  },
});

// Helper function to determine variable type based on name
function determineVariableType(variableName) {
  if (/DATE|TIME/.test(variableName)) return 'date';
  if (/AMOUNT|PRICE|COST|NUM|COUNT/.test(variableName)) return 'number';
  if (/EMAIL|MAIL/.test(variableName)) return 'email';
  return 'text';
}

export default VariableNode;