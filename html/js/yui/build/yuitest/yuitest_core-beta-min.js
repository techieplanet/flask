/*
Copyright (c) 2008, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
version: 2.5.0
*/
YAHOO.namespace("tool");YAHOO.tool.TestCase=function(A){this._should={};for(var B in A){this[B]=A[B];}if(!YAHOO.lang.isString(this.name)){this.name=YAHOO.util.Dom.generateId(null,"testCase");}};YAHOO.tool.TestCase.prototype={resume:function(A){YAHOO.tool.TestRunner.resume(A);},wait:function(B,A){throw new YAHOO.tool.TestCase.Wait(B,A);},setUp:function(){},tearDown:function(){}};YAHOO.tool.TestCase.Wait=function(B,A){this.segment=(YAHOO.lang.isFunction(B)?B:null);this.delay=(YAHOO.lang.isNumber(A)?A:0);};YAHOO.namespace("tool");YAHOO.tool.TestSuite=function(A){this.name="";this.items=[];if(YAHOO.lang.isString(A)){this.name=A;}else{if(YAHOO.lang.isObject(A)){YAHOO.lang.augmentObject(this,A,true);}}if(this.name===""){this.name=YAHOO.util.Dom.generateId(null,"testSuite");}};YAHOO.tool.TestSuite.prototype={add:function(A){if(A instanceof YAHOO.tool.TestSuite||A instanceof YAHOO.tool.TestCase){this.items.push(A);}},setUp:function(){},tearDown:function(){}};YAHOO.namespace("tool");YAHOO.tool.TestRunner=(function(){function B(C){this.testObject=C;this.firstChild=null;this.lastChild=null;this.parent=null;this.next=null;this.results={passed:0,failed:0,total:0,ignored:0};if(C instanceof YAHOO.tool.TestSuite){this.results.type="testsuite";this.results.name=C.name;}else{if(C instanceof YAHOO.tool.TestCase){this.results.type="testcase";this.results.name=C.name;}}}B.prototype={appendChild:function(C){var D=new B(C);if(this.firstChild===null){this.firstChild=this.lastChild=D;}else{this.lastChild.next=D;this.lastChild=D;}D.parent=this;return D;}};function A(){A.superclass.constructor.apply(this,arguments);this.masterSuite=new YAHOO.tool.TestSuite("YUI Test Results");this._cur=null;this._root=null;var D=[this.TEST_CASE_BEGIN_EVENT,this.TEST_CASE_COMPLETE_EVENT,this.TEST_SUITE_BEGIN_EVENT,this.TEST_SUITE_COMPLETE_EVENT,this.TEST_PASS_EVENT,this.TEST_FAIL_EVENT,this.TEST_IGNORE_EVENT,this.COMPLETE_EVENT,this.BEGIN_EVENT];for(var C=0;C<D.length;C++){this.createEvent(D[C],{scope:this});}}YAHOO.lang.extend(A,YAHOO.util.EventProvider,{TEST_CASE_BEGIN_EVENT:"testcasebegin",TEST_CASE_COMPLETE_EVENT:"testcasecomplete",TEST_SUITE_BEGIN_EVENT:"testsuitebegin",TEST_SUITE_COMPLETE_EVENT:"testsuitecomplete",TEST_PASS_EVENT:"pass",TEST_FAIL_EVENT:"fail",TEST_IGNORE_EVENT:"ignore",COMPLETE_EVENT:"complete",BEGIN_EVENT:"begin",_addTestCaseToTestTree:function(C,D){var E=C.appendChild(D);for(var F in D){if(F.indexOf("test")===0&&YAHOO.lang.isFunction(D[F])){E.appendChild(F);}}},_addTestSuiteToTestTree:function(C,F){var E=C.appendChild(F);for(var D=0;D<F.items.length;D++){if(F.items[D] instanceof YAHOO.tool.TestSuite){this._addTestSuiteToTestTree(E,F.items[D]);}else{if(F.items[D] instanceof YAHOO.tool.TestCase){this._addTestCaseToTestTree(E,F.items[D]);}}}},_buildTestTree:function(){this._root=new B(this.masterSuite);this._cur=this._root;for(var C=0;C<this.masterSuite.items.length;C++){if(this.masterSuite.items[C] instanceof YAHOO.tool.TestSuite){this._addTestSuiteToTestTree(this._root,this.masterSuite.items[C]);}else{if(this.masterSuite.items[C] instanceof YAHOO.tool.TestCase){this._addTestCaseToTestTree(this._root,this.masterSuite.items[C]);}}}},_handleTestObjectComplete:function(C){if(YAHOO.lang.isObject(C.testObject)){C.parent.results.passed+=C.results.passed;C.parent.results.failed+=C.results.failed;C.parent.results.total+=C.results.total;C.parent.results.ignored+=C.results.ignored;C.parent.results[C.testObject.name]=C.results;if(C.testObject instanceof YAHOO.tool.TestSuite){C.testObject.tearDown();this.fireEvent(this.TEST_SUITE_COMPLETE_EVENT,{testSuite:C.testObject,results:C.results});}else{if(C.testObject instanceof YAHOO.tool.TestCase){this.fireEvent(this.TEST_CASE_COMPLETE_EVENT,{testCase:C.testObject,results:C.results});}}}},_next:function(){if(this._cur.firstChild){this._cur=this._cur.firstChild;}else{if(this._cur.next){this._cur=this._cur.next;}else{while(this._cur&&!this._cur.next&&this._cur!==this._root){this._handleTestObjectComplete(this._cur);this._cur=this._cur.parent;}if(this._cur==this._root){this._cur.results.type="report";this._cur.results.timestamp=(new Date()).toLocaleString();this.fireEvent(this.COMPLETE_EVENT,{results:this._cur.results});this._cur=null;}else{this._handleTestObjectComplete(this._cur);this._cur=this._cur.next;}}}return this._cur;},_run:function(){var E=false;var D=this._next();if(D!==null){var C=D.testObject;if(YAHOO.lang.isObject(C)){if(C instanceof YAHOO.tool.TestSuite){this.fireEvent(this.TEST_SUITE_BEGIN_EVENT,{testSuite:C});C.setUp();}else{if(C instanceof YAHOO.tool.TestCase){this.fireEvent(this.TEST_CASE_BEGIN_EVENT,{testCase:C});}}if(typeof setTimeout!="undefined"){setTimeout(function(){YAHOO.tool.TestRunner._run();},0);}else{this._run();}}else{this._runTest(D);}}},_resumeTest:function(G){var C=this._cur;var H=C.testObject;var E=C.parent.testObject;var K=(E._should.fail||{})[H];var D=(E._should.error||{})[H];var F=false;var I=null;try{G.apply(E);if(K){I=new YAHOO.util.ShouldFail();F=true;}else{if(D){I=new YAHOO.util.ShouldError();F=true;}}}catch(J){if(J instanceof YAHOO.util.AssertionError){if(!K){I=J;F=true;}}else{if(J instanceof YAHOO.tool.TestCase.Wait){if(YAHOO.lang.isFunction(J.segment)){if(YAHOO.lang.isNumber(J.delay)){if(typeof setTimeout!="undefined"){setTimeout(function(){YAHOO.tool.TestRunner._resumeTest(J.segment);},J.delay);}else{throw new Error("Asynchronous tests not supported in this environment.");}}}return ;}else{if(!D){I=new YAHOO.util.UnexpectedError(J);F=true;}else{if(YAHOO.lang.isString(D)){if(J.message!=D){I=new YAHOO.util.UnexpectedError(J);F=true;}}else{if(YAHOO.lang.isFunction(D)){if(!(J instanceof D)){I=new YAHOO.util.UnexpectedError(J);F=true;}}else{if(YAHOO.lang.isObject(D)){if(!(J instanceof D.constructor)||J.message!=D.message){I=new YAHOO.util.UnexpectedError(J);F=true;}}}}}}}}if(F){this.fireEvent(this.TEST_FAIL_EVENT,{testCase:E,testName:H,error:I});}else{this.fireEvent(this.TEST_PASS_EVENT,{testCase:E,testName:H});}E.tearDown();C.parent.results[H]={result:F?"fail":"pass",message:I?I.getMessage():"Test passed",type:"test",name:H};
if(F){C.parent.results.failed++;}else{C.parent.results.passed++;}C.parent.results.total++;if(typeof setTimeout!="undefined"){setTimeout(function(){YAHOO.tool.TestRunner._run();},0);}else{this._run();}},_runTest:function(F){var C=F.testObject;var D=F.parent.testObject;var G=D[C];var E=(D._should.ignore||{})[C];if(E){F.parent.results[C]={result:"ignore",message:"Test ignored",type:"test",name:C};F.parent.results.ignored++;F.parent.results.total++;this.fireEvent(this.TEST_IGNORE_EVENT,{testCase:D,testName:C});if(typeof setTimeout!="undefined"){setTimeout(function(){YAHOO.tool.TestRunner._run();},0);}else{this._run();}}else{D.setUp();this._resumeTest(G);}},fireEvent:function(C,D){D=D||{};D.type=C;A.superclass.fireEvent.call(this,C,D);},add:function(C){this.masterSuite.add(C);},clear:function(){this.masterSuite.items=[];},resume:function(C){this._resumeTest(C||function(){});},run:function(C){var D=YAHOO.tool.TestRunner;D._buildTestTree();D.fireEvent(D.BEGIN_EVENT);D._run();}});return new A();})();YAHOO.namespace("util");YAHOO.util.Assert={fail:function(A){throw new YAHOO.util.AssertionError(A||"Test force-failed.");},areEqual:function(B,C,A){if(B!=C){throw new YAHOO.util.ComparisonFailure(A||"Values should be equal.",B,C);}},areNotEqual:function(A,C,B){if(A==C){throw new YAHOO.util.UnexpectedValue(B||"Values should not be equal.",A);}},areNotSame:function(A,C,B){if(A===C){throw new YAHOO.util.UnexpectedValue(B||"Values should not be the same.",A);}},areSame:function(B,C,A){if(B!==C){throw new YAHOO.util.ComparisonFailure(A||"Values should be the same.",B,C);}},isFalse:function(B,A){if(false!==B){throw new YAHOO.util.ComparisonFailure(A||"Value should be false.",false,B);}},isTrue:function(B,A){if(true!==B){throw new YAHOO.util.ComparisonFailure(A||"Value should be true.",true,B);}},isNaN:function(B,A){if(!isNaN(B)){throw new YAHOO.util.ComparisonFailure(A||"Value should be NaN.",NaN,B);}},isNotNaN:function(B,A){if(isNaN(B)){throw new YAHOO.util.UnexpectedValue(A||"Values should not be NaN.",NaN);}},isNotNull:function(B,A){if(YAHOO.lang.isNull(B)){throw new YAHOO.util.UnexpectedValue(A||"Values should not be null.",null);}},isNotUndefined:function(B,A){if(YAHOO.lang.isUndefined(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should not be undefined.",undefined);}},isNull:function(B,A){if(!YAHOO.lang.isNull(B)){throw new YAHOO.util.ComparisonFailure(A||"Value should be null.",null,B);}},isUndefined:function(B,A){if(!YAHOO.lang.isUndefined(B)){throw new YAHOO.util.ComparisonFailure(A||"Value should be undefined.",undefined,B);}},isArray:function(B,A){if(!YAHOO.lang.isArray(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should be an array.",B);}},isBoolean:function(B,A){if(!YAHOO.lang.isBoolean(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should be a Boolean.",B);}},isFunction:function(B,A){if(!YAHOO.lang.isFunction(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should be a function.",B);}},isInstanceOf:function(B,C,A){if(!(C instanceof B)){throw new YAHOO.util.ComparisonFailure(A||"Value isn't an instance of expected type.",B,C);}},isNumber:function(B,A){if(!YAHOO.lang.isNumber(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should be a number.",B);}},isObject:function(B,A){if(!YAHOO.lang.isObject(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should be an object.",B);}},isString:function(B,A){if(!YAHOO.lang.isString(B)){throw new YAHOO.util.UnexpectedValue(A||"Value should be a string.",B);}},isTypeOf:function(A,C,B){if(typeof C!=A){throw new YAHOO.util.ComparisonFailure(B||"Value should be of type "+expected+".",expected,typeof actual);}}};YAHOO.util.AssertionError=function(A){arguments.callee.superclass.constructor.call(this,A);this.message=A;this.name="AssertionError";};YAHOO.lang.extend(YAHOO.util.AssertionError,Error,{getMessage:function(){return this.message;},toString:function(){return this.name+": "+this.getMessage();},valueOf:function(){return this.toString();}});YAHOO.util.ComparisonFailure=function(B,A,C){arguments.callee.superclass.constructor.call(this,B);this.expected=A;this.actual=C;this.name="ComparisonFailure";};YAHOO.lang.extend(YAHOO.util.ComparisonFailure,YAHOO.util.AssertionError,{getMessage:function(){return this.message+"\nExpected: "+this.expected+" ("+(typeof this.expected)+")\nActual:"+this.actual+" ("+(typeof this.actual)+")";}});YAHOO.util.UnexpectedValue=function(B,A){arguments.callee.superclass.constructor.call(this,B);this.unexpected=A;this.name="UnexpectedValue";};YAHOO.lang.extend(YAHOO.util.UnexpectedValue,YAHOO.util.AssertionError,{getMessage:function(){return this.message+"\nUnexpected: "+this.unexpected+" ("+(typeof this.unexpected)+") ";}});YAHOO.util.ShouldFail=function(A){arguments.callee.superclass.constructor.call(this,A||"This test should fail but didn't.");this.name="ShouldFail";};YAHOO.lang.extend(YAHOO.util.ShouldFail,YAHOO.util.AssertionError);YAHOO.util.ShouldError=function(A){arguments.callee.superclass.constructor.call(this,A||"This test should have thrown an error but didn't.");this.name="ShouldError";};YAHOO.lang.extend(YAHOO.util.ShouldError,YAHOO.util.AssertionError);YAHOO.util.UnexpectedError=function(A){arguments.callee.superclass.constructor.call(this,"Unexpected error: "+A.message);this.cause=A;this.name="UnexpectedError";};YAHOO.lang.extend(YAHOO.util.UnexpectedError,YAHOO.util.AssertionError);YAHOO.util.ArrayAssert={contains:function(E,D,B){var C=false;for(var A=0;A<D.length&&!C;A++){if(D[A]===E){C=true;}}if(!C){YAHOO.util.Assert.fail(B||"Value ("+E+") not found in array.");}},containsItems:function(C,D,B){for(var A=0;A<C.length;A++){this.contains(C[A],D,B);}if(!found){YAHOO.util.Assert.fail(B||"Value not found in array.");}},containsMatch:function(E,D,B){if(typeof E!="function"){throw new TypeError("ArrayAssert.containsMatch(): First argument must be a function.");}var C=false;for(var A=0;A<D.length&&!C;A++){if(E(D[A])){C=true;}}if(!C){YAHOO.util.Assert.fail(B||"No match found in array.");}},doesNotContain:function(E,D,B){var C=false;
for(var A=0;A<D.length&&!C;A++){if(D[A]===E){C=true;}}if(C){YAHOO.util.Assert.fail(B||"Value found in array.");}},doesNotContainItems:function(C,D,B){for(var A=0;A<C.length;A++){this.doesNotContain(C[A],D,B);}},doesNotContainMatch:function(E,D,B){if(typeof E!="function"){throw new TypeError("ArrayAssert.doesNotContainMatch(): First argument must be a function.");}var C=false;for(var A=0;A<D.length&&!C;A++){if(E(D[A])){C=true;}}if(C){YAHOO.util.Assert.fail(B||"Value found in array.");}},indexOf:function(E,D,A,C){for(var B=0;B<D.length;B++){if(D[B]===E){YAHOO.util.Assert.areEqual(A,B,C||"Value exists at index "+B+" but should be at index "+A+".");return ;}}YAHOO.util.Assert.fail(C||"Value doesn't exist in array.");},itemsAreEqual:function(D,E,C){var A=Math.max(D.length,E.length);for(var B=0;B<A;B++){YAHOO.util.Assert.areEqual(D[B],E[B],C||"Values in position "+B+" are not equal.");}},itemsAreEquivalent:function(E,F,B,D){if(typeof B!="function"){throw new TypeError("ArrayAssert.itemsAreEquivalent(): Third argument must be a function.");}var A=Math.max(E.length,F.length);for(var C=0;C<A;C++){if(!B(E[C],F[C])){throw new YAHOO.util.ComparisonFailure(D||"Values in position "+C+" are not equivalent.",E[C],F[C]);}}},isEmpty:function(B,A){if(B.length>0){YAHOO.util.Assert.fail(A||"Array should be empty.");}},isNotEmpty:function(B,A){if(B.length===0){YAHOO.util.Assert.fail(A||"Array should not be empty.");}},itemsAreSame:function(D,E,C){var A=Math.max(D.length,E.length);for(var B=0;B<A;B++){YAHOO.util.Assert.areSame(D[B],E[B],C||"Values in position "+B+" are not the same.");}},lastIndexOf:function(E,D,A,C){for(var B=D.length;B>=0;B--){if(D[B]===E){YAHOO.util.Assert.areEqual(A,B,C||"Value exists at index "+B+" but should be at index "+A+".");return ;}}YAHOO.util.Assert.fail(C||"Value doesn't exist in array.");}};YAHOO.namespace("util");YAHOO.util.ObjectAssert={propertiesAreEqual:function(D,F,C){var B=[];for(var E in D){B.push(E);}for(var A=0;A<B.length;A++){YAHOO.util.Assert.isNotUndefined(F[B[A]],C||"Property'"+B[A]+"' expected.");}},hasProperty:function(A,B,C){if(YAHOO.lang.isUndefined(B[A])){YAHOO.util.Assert.fail(C||"Property "+A+" not found on object.");}},hasOwnProperty:function(A,B,C){if(!YAHOO.lang.hasOwnProperty(B,A)){YAHOO.util.Assert.fail(C||"Property "+A+" not found on object instance.");}}};YAHOO.util.DateAssert={datesAreEqual:function(B,C,A){if(B instanceof Date&&C instanceof Date){YAHOO.util.Assert.areEqual(B.getFullYear(),C.getFullYear(),A||"Years should be equal.");YAHOO.util.Assert.areEqual(B.getMonth(),C.getMonth(),A||"Months should be equal.");YAHOO.util.Assert.areEqual(B.getDate(),C.getDate(),A||"Day of month should be equal.");}else{throw new TypeError("DateAssert.datesAreEqual(): Expected and actual values must be Date objects.");}},timesAreEqual:function(B,C,A){if(B instanceof Date&&C instanceof Date){YAHOO.util.Assert.areEqual(B.getHours(),C.getHours(),A||"Hours should be equal.");YAHOO.util.Assert.areEqual(B.getMinutes(),C.getMinutes(),A||"Minutes should be equal.");YAHOO.util.Assert.areEqual(B.getSeconds(),C.getSeconds(),A||"Seconds should be equal.");}else{throw new TypeError("DateAssert.timesAreEqual(): Expected and actual values must be Date objects.");}}};YAHOO.register("yuitest_core",YAHOO.tool.TestRunner,{version:"2.5.0",build:"895"});